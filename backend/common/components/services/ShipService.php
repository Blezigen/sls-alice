<?php

namespace common\components\services;

use api\exceptions\NotFoundHttpException;
use common\contracts\IOrderService;
use common\exceptions\CabinStatusException;
use common\exceptions\ShipServiceException;
use common\exceptions\ValidationException;
use common\IConstant;
use common\models\Cabin;
use common\models\CabinExclude;
use common\models\CabinExcludeGroup;
use common\models\CabinPlace;
use common\models\Collection;
use common\models\Order;
use common\models\Place;
use common\models\Ship;
use common\models\Tour;
use common\models\VirtualCabin;
use SebastianBergmann\CodeCoverage\Report\PHP;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class ShipService extends Model
{
    /**
     * @var IOrderService|object
     */
    private $orderService;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->orderService = \Yii::$container->get(IOrderService::class);
    }

    /**
     * @param $shipId
     * @param $onlyAllInclude
     *
     * @return array|CabinStatus[]
     *
     * @throws Exception
     */
    public function analyseCabins(
        $shipId,
        array $numbers,
        $onlyAllInclude = true
    ): array {
        /** @var Cabin[] $cabins */
        $cabins = $this->getCabins($shipId, $numbers, $onlyAllInclude, [
            'cabinExcludes',
            'tours',
        ]);

        $resultData = [];
        foreach ($cabins as $cabin) {
            $result = new CabinStatus($cabin);
            $result->checkProblem();
            $resultData[] = $result;
        }

        return $resultData;
    }

    /**
     * @param $shipId
     * @param $number
     *
     * @throws Exception
     */
    public function analyseCabinByNumber(
        string|int $shipId,
        string|int $number,
    ): CabinStatus {
        /** @var Cabin $cabin */
        $cabin = $this->getCabin($shipId, $number, [
            'cabinExcludes',
            'tours',
            'orderIfReserved' => function ($q) {
                $q->select(["id", "tour_id"]);
            },
        ]);

        if (!$cabin) {
            throw new \Exception("Каюта $number не найдена");
        }

        return $this->analyseCabin($cabin);
    }

    /**
     * @param $shipId
     * @param $number
     *
     * @throws Exception
     */
    public function analyseCabin( Cabin $cabin ): CabinStatus {
        \Yii::beginProfile("new CabinStatus({$cabin->number})", __METHOD__);
        $result = new CabinStatus($cabin);
        $result->checkProblem();
        \Yii::endProfile("new CabinStatus({$cabin->number})", __METHOD__);
        return $result;
    }

    /**
     * @param $shipId
     *
     * @return array[]
     *
     * @throws Exception
     * @throws \common\exceptions\NotImplementException
     */
    public function analyseIntersections($shipId, array $numbers)
    {
        $this->findShip($shipId);

        $cabins = [];
        foreach ($numbers as $number) {
            $cabin = $this->findCabin($number, $shipId);
            $cabins[$cabin->id] = $cabin->number;
        }

        $tours = $this->findTours($shipId);

        $data = [];
        foreach ($cabins as $cabinId => $cabinNumber) {
            $data[$cabinNumber]['reserve'] = false;
            $data[$cabinNumber]['lock'] = false;
            $data[$cabinNumber]['split'] = false;

            $virtualCabins = $this->findVirtualCabins($cabinId);

            if ($virtualCabins) {
                foreach ($virtualCabins as $virtualCabin) {
                    if (!is_array($data[$cabinNumber]['split'])) {
                        $data[$cabinNumber]['split'] = [];
                    }
                    $data[$cabinNumber]['split'][] = [
                        'virtual_cabin_id' => $virtualCabin->id,
                        'name'             => $virtualCabin->number,
                    ];
                }
            }

            $tourIds = ArrayHelper::getColumn($tours, 'id');
            /** @var Order[] $reserves */
            $reserves = $this->orderService->getReservesByTourIds($tourIds);
            foreach ($reserves as $reserve) {
                if ($reserve->hasCabinId($cabinId)) {
                    if (!is_array($data[$cabinNumber]['reserve'])) {
                        $data[$cabinNumber]['reserve'] = [];
                    }

                    $data[$cabinNumber]['reserve'][]
                        = ['tour_id' => $reserve->tour_id];
                }
            }

            $cabinExcludes = $this->findCabinExclude($cabinId);

            if ($cabinExcludes) {
                $data[$cabinNumber]['lock'] = true;
            }
        }

        return ['data' => $data];
    }

    public function getCabinByNumber($shipId, $number)
    {
        /** @var Cabin $cabin */
        $cabin = Cabin::find()->number($number)->shipId($shipId)
            ->one();
        if (!$cabin) {
            throw new \Exception('Каюта не найдена');
        }

        return $cabin;
    }

    /**
     * @param $shipId
     *
     * @return array
     *
     * @throws Exception
     */
    public function getCabinsStatuses($shipId, array $numbers)
    {
        $response = $this->analyseIntersections($shipId, $numbers);

        foreach ($numbers as $number) {
            $cabinStatus = $response['data'][$number];

            if ($cabinStatus['reserve']) {
                foreach ($cabinStatus['reserve'] as $value) {
                    $message[$number]['status_title']
                        = "Зарезервирована в туре id: {$value['tour_id']}";
                    $message[$number]['status']
                        = IConstant::CABIN_STATUS_RESERVED;
                }
            }

            if (!$cabinStatus['reserve'] && !$cabinStatus['lock']) {
                $message[$number]['status_title'] = 'Доступна для продажи';
                $message[$number]['status'] = IConstant::CABIN_STATUS_AVAILABLE;
            }

            if ($cabinStatus['lock']) {
                $message[$number]['status_title'] = 'Заблокированна';
                $message[$number]['status'] = IConstant::CABIN_STATUS_BLOCKED;
            }
        }

        return $message;
    }

    /**
     * @param $shipId
     * @param $number
     * @param $classCabinId
     * @param $rectangle
     * @param  false  $save
     *
     * @throws Exception
     * @throws ValidationException
     * @throws \Throwable
     */
    public function addCabin(
        $shipId,
        $number,
        $classCabinId,
        $rectangle,
        $save = false
    ) {
        $this->findShip($shipId);

        /** @var Cabin $cabin */
        $cabin = Cabin::find()
            ->withoutTrashed()
            ->andWhere(['number' => $number])
            ->andWhere(['ship_id' => $shipId])
            ->one();

        if ($cabin) {
            throw new Exception("Каюта с номером {$number} уже существует");
        }

        $cabin = new Cabin();
        $cabin->ship_id = $shipId;
        $cabin->number = $number;
        $cabin->ship_cabin_class_id = $classCabinId;
//        $cabin->rectangle = $rectangle;

        $cabin->validate();

        if ($save) {
            if ($cabin->save()) {
                return [
                    'message' => 'Каюта добавлена',
                    'data'    => [
                        "id" => $cabin->id
                    ],
                ];
            }
        }

        return [
            "message" => "Добавление будет произведено успешно"
        ];
    }

    /**
     * @param $shipId
     * @param $number
     * @param  false  $save
     *
     * @return array|string
     *
     * @throws Exception
     */
    public function deleteCabin($shipId, $number, $save = false)
    {
        $this->findShip($shipId);

        $cabin = $this->findCabin($number, $shipId);

        $response = $this->analyseIntersections($shipId, [$number]);
        $cabinStatus = $response['data'][$number];

        if ($cabinStatus['reserve']) {
            throw new Exception("Невозможно удалить каюта {$number} уже зарезервированна в туре");
        }

        if ($cabinStatus['lock']) {
            throw new Exception("Невозможно удалить, каюта {$number} заблокирвана");
        }

        if ($cabinStatus['split']) {
            throw new Exception("Невозможно удалить, каюта {$number} разбита на виртуальные");
        }

        if ($save) {
            $cabin->delete();

            return [
                'message' => "Каюта {$number} удалена",
            ];
        }

        return [
            'message' => "Каюту {$number} можно удалить",
        ];
    }

    /**
     * @param $shipId
     * @param $tourId
     * @param $blockNameId
     * @param $numbers
     * @param  false  $save
     *
     * @return array
     *
     * @throws Exception
     * @throws ValidationException
     * @throws \Throwable
     */
    public function lockCabins(
        $shipId,
        $tourId,
        string|int $blockName,
        $numbers,
        $save = false
    ) {
        $this->findShip($shipId);

        $tour = $this->findTour($tourId);

        $blockGroup = $this->findOrCreateExGroupByName($blockName);

        $message = [];

        foreach ($numbers as $number) {
            $cabin = $this->findCabin($number, $shipId);
            $data = $this->analyseCabinByNumber($shipId, $cabin->number);

            if ($data->cabinReservedInTour($tour)) {
                throw new Exception(\Yii::t("app",
                    "Нельзя блокировать каюту №{number} т.к. она зарезервирована в заказе №{order}!",
                    [
                        "number" => $cabin->number,
                        "order"  => implode(",", $data->reserved[$tour->id])
                    ]));
            }

            if ($data->cabinBlockedInTour($tour)) {
                throw new Exception(\Yii::t("app",
                    "Нельзя блокировать каюту №{number} т.к. она уже заблокирована!",
                    ["number" => $cabin->number]));
            }

            if ($data->cabinHasVirtualInTour($tour)) {
                throw new Exception(\Yii::t("app",
                    "Каюта №{number} разделена на виртуальные, блокировка невозможна!",
                    ["number" => $cabin->number]));
            }

            $cabinExcludes = new CabinExclude([
                "cabin_id" => $cabin->id,
                "tour_id" => $tourId,
                "group_id" => $blockGroup->id
            ]);

            if (!$cabinExcludes->validate()) {
                throw new ValidationException($cabinExcludes->errors);
            }
            if ($save && $cabinExcludes->save()) {
                $message[] = "Каюта {$cabin->number} - заблокирована";
                continue;
            }
            $message[] = "Каюта (id: {$cabin->id}) с номером {$cabin->number} - готова к блокировке";
        }

        return $message;
    }

    /**
     * @param  string|int  $shipId
     * @param  string|int  $tourId
     * @param  string  $blockName
     * @param  string|int  $number
     * @param  bool  $save
     *
     * @return void
     */
    public function freeCabin(
        string|int $shipId,
        string|int $tourId,
        string|int $number,
        string $blockName,
        bool $save = false
    ) {
        $this->findShip($shipId);
        $this->findTour($tourId);
        $cabin = $this->findCabin($number, $shipId);

        $cabinStatusData = $this->analyseCabinByNumber($shipId, $cabin->number);

        $crossings = $cabinStatusData->crossing[$tourId] ??[];

        echo count($crossings).PHP_EOL;
        foreach ($crossings as $crossingTourId) {
            echo "Освобождение каюты: $crossingTourId - ";
            try {
                $result = $this->lockCabins($shipId, $crossingTourId, $blockName,
                    [$cabin->number], $save);
                echo "SUCCESS".PHP_EOL;
            } catch (\Throwable $ex){
                echo "SKIP ({$ex->getMessage()})".PHP_EOL;
            }

        }

        return true;
    }

    /**
     * @param $shipId
     * @param $tourId
     * @param $blockNameId
     * @param  false  $save
     *
     * @return array
     *
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function unlockCabins(
        $shipId,
        $tourId,
        $blockName,
        array $numbers,
        $save = false
    ) {
        $this->findShip($shipId);

        $this->findTour($tourId);

        $blockGroup = $this->findOrCreateExGroupByName($blockName);

        foreach ($numbers as $number) {
            $cabin = $this->findCabin($number, $shipId);

            $cabins[$cabin->id] = $cabin->number;
        }

        foreach ($cabins as $cabinId => $cabinNumber) {
            $cabinexcludes = $this->findCabinExclude($cabinId);

            if (!$cabinexcludes) {
                throw new Exception("Каюта {$cabinNumber} не была заблокирована");
            } else {
                if ($save) {
                    $cabinexcludes->delete();
                    $message[] = "Каюта {$cabinNumber} разблокирована";
                } else {
                    $message[] = "Каюта id: {$cabinId} номер: {$cabinNumber}";
                }
            }
        }

        return $message;
    }

    /**
     * @param $shipId
     * @param $tourId
     * @param $number
     * @param $placeNumbers
     * @param  false  $save
     *
     * @return array|array[]|string
     *
     * @throws Exception
     * @throws ValidationException
     * @throws \Throwable
     * @throws CabinStatusException
     */
    public function splitCabin(
        $shipId,
        $tourId,
        $number,
        $genderName,
        $placeNumbers,
        bool $save = false
    ) {
        $this->findShip($shipId);
        $tour = $this->getTour($tourId);

        $gender = Collection::find()
            ->collection(IConstant::COLLECTION_GENDERS)
            ->slug($genderName)
            ->one();

        if (!$gender) {
            throw new Exception('Пол не опознан!');
        }

        $cabinStatusData = $this->analyseCabinByNumber($shipId, $number);

        if ($cabinStatusData->hasProblem()) {
            throw new CabinStatusException($cabinStatusData->problem,
                $cabinStatusData->hints);
        }

        $cabin = $cabinStatusData->cabin;
        $virtualCabinIds = $cabinStatusData->virtualCabins[$tour->id] ?? [];

        $vCabins = VirtualCabin::find()
            ->select(["id", "number"])
            ->andWhere(["tour_id" => $tour->id])
            ->andWhere(["in", "id", $virtualCabinIds])
            ->all();

        $existVirtualCabinNumbers = ArrayHelper::getColumn($vCabins, "number");
//        $response = $this->analyseIntersections($shipId, [$number]);

        if ($cabinStatusData->cabinBlockedInTour($tour)) {
            throw new Exception("Невозможно разделить, каюта {$number} заблокирована");
        }

        if ($cabinStatusData->cabinReservedInTour($tour)) {
            throw new Exception("Невозможно разделить, каюта {$number} зарезервирована в туре");
        }

        foreach ($placeNumbers as $placeNumber) {
            if (!$cabin->hasPlaceNumber($placeNumber)) {
                throw new NotFoundHttpException("Не найдено место с номером:$placeNumber в каюте");
            }
        }

//        if ($cabinStatusData->cabinSplitInTour($tour)) {
//            throw new Exception("Невозможно разделить, каюта {$number} каюта уже разбита на виртуальные");
//        }

        $virtualCabins = [];
        $cabinPlaces = $cabin->shipCabinClass->places;

        if (empty($cabinPlaces))
            throw new ShipServiceException("Перед разделением, необходимо указать настройку мест у класса каюты");

        foreach ($cabinPlaces as $place) {
            if (!in_array($place->number, $placeNumbers)) {
                continue;
            }

            $number = $this->virtualName($cabin->number, $place->number);

            if (in_array($number, $existVirtualCabinNumbers)) {
                throw new Exception("Найдена разделённая каюта $number");
            }

            $virtualCabin = new VirtualCabin([
                'cabin_id'        => $cabin->id,
                'tour_id'         => $tourId,
                'gender_type_cid' => $gender->id,
                'number'          => $number,
                'cabin_place_id'  => $place->id,
            ]);

            if (!$virtualCabin->validate()) {
                throw new ValidationException($virtualCabin->errors);
            }

            if ($save) {
                $virtualCabin->save();
            }

            $virtualCabins[] = $virtualCabin->toArray();
        }

        if ($save) {
            return [
                'message' => 'Каюта разделена',
                'cabins'  => $virtualCabins,
            ];
        }

        return [
            'cabins' => $virtualCabins,
        ];
    }

    /**
     * @param $placeIds
     *
     * @return bool
     *
     * @throws Exception
     */
    public function findPlaces($placeIds)
    {
        foreach ($placeIds as $placeId) {
            $place = Place::find()
                ->withoutTrashed()
                ->byId($placeId)
                ->one();

            if (!$place) {
                throw new Exception(\Yii::t('app',
                    'Место id {place} не найдено', ['place' => $placeId]));
            }
        }

        return true;
    }

    /**
     * @param $placeIds
     *
     * @return array|CabinPlace[]
     *
     * @throws Exception
     */
    public function findCabinPlaces($placeIds): array
    {
        /** @var CabinPlace[] $places */
        $places = CabinPlace::find()
            ->withoutTrashed()
            ->andWhere(['IN', 'id', $placeIds])
            ->all();

        return $places;
    }

    /**
     * @param $shipId
     *
     * @return Ship
     *
     * @throws Exception
     */
    public function findShip($shipId)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()
            ->byId($shipId)
            ->one();

        if (!$ship) {
            throw new Exception('Корабль не найден');
        }

        return $ship;
    }

    /**
     * @param $shipId
     *
     * @return Ship
     *
     * @throws Exception
     */
    public function getShip($shipId)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()
            ->byId($shipId)
            ->one();

        if (!$ship) {
            throw new Exception('Корабль не найден');
        }

        return $ship;
    }

    public function findTour($tourId): ?Tour
    {
        /** @var Tour $tour */
        $tour = Tour::find()
            ->byId($tourId)
            ->one();

        return $tour;
    }

    /**
     * @param $tourId
     *
     * @return Tour
     *
     * @throws Exception
     */
    public function getTour($tourId)
    {
        $tour = $this->findTour($tourId);

        if (!$tour) {
            throw new Exception('Тур не найден');
        }

        return $tour;
    }

    /**
     * @param $shipId
     *
     * @return Tour[]
     */
    public function findTours($shipId)
    {
        /** @var Tour[] $tours */
        $tours = Tour::find()
            ->withoutTrashed()
            ->andWhere(['ship_id' => $shipId])
            ->all();

        return $tours;
    }

    /**
     * @param $blockNameId
     *
     * @return CabinExcludeGroup
     *
     * @throws Exception
     */
    public function findCabinExcludeGroupById($blockNameId)
    {
        /** @var CabinExcludeGroup $blocknamed */
        $blocknamed = CabinExcludeGroup::find()
            ->withoutTrashed()
            ->byId($blockNameId)
            ->one();

        if (!$blocknamed) {
            throw new Exception('Название блокировки не найдено');
        }

        return $blocknamed;
    }

    /**
     * @param $blockNameId
     *
     * @throws Exception
     */
    public function findOrCreateExGroupByName(string $blockName
    ): CabinExcludeGroup {
        /** @var CabinExcludeGroup $temp */
        $temp = CabinExcludeGroup::find()
            ->withoutTrashed()
            ->andWhere(['title' => $blockName])
            ->one();

        if (!$temp) {
            $temp = new CabinExcludeGroup([
                'title' => $blockName,
            ]);
            $temp->save();
        }

        return $temp;
    }

    /**
     * @param $number
     * @param $shipId
     *
     * @return Cabin
     *
     * @throws Exception
     */
    public function findCabin($number, $shipId)
    {
        /** @var Cabin $cabin */
        $cabin = Cabin::find()
            ->withoutTrashed()
            ->andWhere(['number' => $number])
            ->andWhere(['ship_id' => $shipId])
            ->one();

        if (!$cabin) {
            throw new Exception("Каюта {$number} не найдена");
        }

        return $cabin;
    }

    /**
     * @param $cabinId
     *
     * @return CabinExclude
     */
    public function findCabinExclude($cabinId)
    {
        /** @var CabinExclude $cabinexcludes */
        $cabinexcludes = CabinExclude::find()
            ->withoutTrashed()
            ->andWhere(['cabin_id' => $cabinId])
            ->one();

        return $cabinexcludes;
    }

    /**
     * @param $cabinId
     * @param $tourId
     *
     * @return array|\common\database\VirtualCabin|false
     */
    public function findVirtualCabin($cabinId, $tourId)
    {
        $virtualCabin = VirtualCabin::find()
            ->andWhere(['cabin_id' => $cabinId])
            ->andWhere(['tour_id' => $tourId])
            ->one();

        if (!$virtualCabin) {
            return false;
        }

        return $virtualCabin;
    }

    /**
     * @param $cabinId
     *
     * @return array|\common\database\VirtualCabin[]|false
     */
    public function findVirtualCabins($cabinId)
    {
        $virtualCabins = VirtualCabin::find()
            ->andWhere(['cabin_id' => $cabinId])
            ->all();

        if (!$virtualCabins) {
            return false;
        }

        return $virtualCabins;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function virtualName($number, $placeNumber)
    {
        return "{$number}{$placeNumber}";
    }

    /**
     * Поиск корабля по идентификатору
     *
     * @param  mixed  $shipId  Идентификатор корабля
     *
     * @return Ship|null
     */
    public function findShipById($shipId)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()
            ->byId($shipId)
            ->one();

        return $ship;
    }

    /**
     * Производит поиск корабля по идентификатору, в случае если корабль не найден, то возвращает ошибку
     *
     * @param  mixed  $shipId  Идентификатор корабля
     *
     * @return Ship
     *
     * @throws Exception
     */
    public function getShipById($shipId)
    {
        $ship = $this->findShipById($shipId);

        if (!$ship) {
            throw new Exception(\Yii::t('app', 'Ship id={id} not found',
                ['id' => $shipId]));
        }

        return $ship;
    }

    /**
     * @param $shipId
     * @param $onlyAllInclude
     *
     * @return array|Cabin[]
     *
     * @throws Exception
     */
    private function getCabins(
        $shipId,
        array $numbers,
        $onlyAllInclude = false,
        $with = []
    ): array {
        $ship = $this->getShip($shipId);
        $cabins = Cabin::find()
            ->with($with)
            ->shipId($ship->id)
            ->andWhere(['IN', 'number', $numbers])
            ->all();

        if ($onlyAllInclude) {
            $dbNumbers = ArrayHelper::getColumn($cabins, 'number');
            $diff = array_diff($numbers, $dbNumbers);

            if (count($diff) > 0) {
                throw new \Exception(\Yii::t('app',
                    'Не найдены каюты с номерами: {cabins}',
                    ['cabins' => implode(',', $diff)]));
            }
        }

        return $cabins;
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    private function getCabin(
        string|int $shipId,
        string|int $number,
        array $with = []
    ): Cabin {
        $ship = $this->getShip($shipId);
        $cabin = Cabin::find()
            ->with($with)
            ->shipId($ship->id)
            ->andWhere(['number' => $number])
            ->one();

        if (!$cabin) {
            throw new \Exception(\Yii::t('app',
                'Не найдена каюта с номером: {cabin}', ['cabin' => $number]));
        }

        return $cabin;
    }
}
