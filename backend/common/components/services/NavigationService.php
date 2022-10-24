<?php

namespace common\components\services;

use Carbon\Carbon;
use common\exceptions\ValidationException;
use common\IConstant;
use common\models\City;
use common\models\Collection;
use common\models\Ship;
use common\models\ShipNavigation;
use common\models\Tour;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;

class NavigationService
{
    /**
     * Добавление навигационной точки.
     *
     * @param $shipId
     * @param $cityId
     * @param $arrivalDT
     * @param $departureDT
     * @param $type
     * @param  null  $tourId
     *
     * @throws Exception
     * @throws ValidationException
     * @throws \Throwable
     */
    public function add($shipId, $cityId, $arrivalDT, $departureDT, $type, $tourId = null)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()
            ->byId($shipId)
            ->one();

        if (!$ship) {
            throw new Exception('Попытка добавить навигацию к несуществующему кораблю');
        }

        if ($cityId) {
            /** @var City $city */
            $city = City::find()
                ->withoutTrashed()
                ->byId($cityId)
                ->one();

            if (!$city) {
                throw new Exception('Попытка добавить навигацию к несуществующему городу');
            }
        }

        $type = Collection::find()->collection(IConstant::COLLECTION_TYPE_NAVIGATION)
            ->id($type)->one();
        if (!$type) {
            throw new Exception('Тип очки обязателен к заполнению');
        }

        if (Carbon::parse($arrivalDT)->diffInSeconds($departureDT) < 0) {
            throw new Exception('arrivalDT не может быть больше departureDT');
        }

        /** @var ShipNavigation $navigation */
        $navigation = ShipNavigation::find()
            ->withoutTrashed()

            ->andWhere(['ship_id' => $shipId])
            ->one();

        if ($navigation != null && $arrivalDT < $navigation->departure_dt) {
            throw new Exception('Ошибка, данная точка перекрывает уже существующую навигацию');
        }

        $navigation = new ShipNavigation();
        $navigation->ship_id = $shipId;
        $navigation->city_id = $cityId;
        $navigation->type_cid = $type->id;
        $navigation->arrival_dt = $arrivalDT;
        $navigation->departure_dt = $departureDT;

        if ($tourId) {
            /** @var Tour $tour */
            $tour = Tour::find()
                ->withoutTrashed()

                ->byId($tourId)
                ->one();

            if (!$tour) {
                throw new Exception('Ошибка, тур не найден');
            }

            $navigation->tour_id = $tourId;
        }

        if (!$navigation->validate()) {
            throw new ValidationException($navigation->errors);
        }

        if ($navigation->save()) {
            return [
                'message' => 'Навигация сохранена',
                'navigation' => $navigation,
            ];
        }

        throw new Exception('Ошибка сохранения');
    }

    /**
     * Добавить прогулку
     *
     * @param $shipId
     * @param $cityId
     * @param $offsetDT
     * @param $duration - Указывается в минутах
     * @param  false  $save
     *
     * @return array
     *
     * @throws Exception
     * @throws ValidationException
     */
    public function addRide($shipId, $cityId, $offsetDT, $duration, $save = false)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()

            ->byId($shipId)
            ->one();

        if (!$ship) {
            throw new Exception('Попытка добавить навигацию к несуществующему кораблю');
        }

        /** @var City $city */
        $city = City::find()
            ->withoutTrashed()

            ->byId($cityId)
            ->one();

        if (!$city) {
            throw new Exception('Попытка добавить навигацию к несуществующему городу');
        }

        $departureDt = Carbon::parse($offsetDT)->addMinutes($duration);

        $navigations[] = [
            'ship_id' => $shipId,
            'city_id' => $cityId,
            'arrival_dt' => $offsetDT,
            'departure_dt' => $offsetDT,
            'type_cid' => Collection::find()->andWhere(['collection' => IConstant::COLLECTION_TYPE_NAVIGATION, 'slug' => IConstant::TYPE_NAVIGATION_STOP])->one()->id,
        ];

        $navigations[] = [
            'ship_id' => $shipId,
            'city_id' => null,
            'arrival_dt' => $offsetDT,
            'departure_dt' => $offsetDT,
            'type_cid' => Collection::find()->andWhere(['collection' => IConstant::COLLECTION_TYPE_NAVIGATION, 'slug' => IConstant::TYPE_NAVIGATION_RIDE])->one()->id,
        ];

        $navigations[] = [
            'ship_id' => $shipId,
            'city_id' => $cityId,
            'arrival_dt' => $offsetDT,
            'departure_dt' => $departureDt->toDateTimeString(),
            'type_cid' => Collection::find()->andWhere(['collection' => IConstant::COLLECTION_TYPE_NAVIGATION, 'slug' => IConstant::TYPE_NAVIGATION_STOP])->one()->id,
        ];

        $message = 'Прогулка - вычислена!';

        if ($save) {
            foreach ($navigations as $navigation) {
                $this->add($navigation['ship_id'], $navigation['city_id'], $navigation['arrival_dt'], $navigation['departure_dt'], $navigation['type_cid']);
            }
            $message = 'Прогулка - сохранена!';
        }

        return [
            'message' => $message,
            'data' => $navigations,
        ];
    }

    /**
     * Удаляет навигационную точку.
     *
     * @param $shipId
     * @param $navigationId
     */
    public function delete($shipId, $navigationId)
    {
        /** @var Ship $ship */
        $ship = Ship::find()
            ->withoutTrashed()

            ->byId($shipId)
            ->one();

        if (!$ship) {
            throw new Exception('Корабль не найден');
        }

        /** @var ShipNavigation $navigation */
        $navigation = ShipNavigation::find()
            ->withoutTrashed() // не выводить те которые уже в мусорке
             // только актуальная версия
            ->byId($navigationId) // получить Навигацию по ID
            ->andWhere(['ship_id' => $shipId])
            ->one();

        if (!$navigation) {
            throw new Exception('Навигация не найдена');
        }

        /** @var Tour $tour */
        $tour = Tour::find()
            ->andWhere(['ship_id' => $shipId])
            ->andWhere(['>=', 'arrival_dt', $navigation->arrival_dt])
            ->andWhere(['<=', 'departure_dt', $navigation->departure_dt])
            ->all();

        if ($tour) {
            throw new Exception('Навигация используется в туре');
        }

        $navigation->delete();

        return ['message' => 'Точка навигации удалена'];
    }

    /**
     * Позволяет по двум навигационным точкам определить имя тура.
     * Необходимо для пред просмотра тура, что-бы вывести название.
     *
     * @param $shipId
     * @param $navigationId
     * @param $navigationId2
     *
     * @return string
     */
    public function getName($shipId, $navigationId, $navigationId2)
    {
        /** @var ShipNavigation $navigation */
        $navigation = ShipNavigation::find()
            ->withoutTrashed()
            ->byId($navigationId)

            ->andWhere(['ship_id' => $shipId])
            ->one();

        /** @var ShipNavigation $navigation2 */
        $navigation2 = ShipNavigation::find()
            ->withoutTrashed()
            ->byId($navigationId2)

            ->andWhere(['ship_id' => $shipId])
            ->one();

        /** @var ShipNavigation $navigation_between */
        $navigation_between = ShipNavigation::find()

            ->andWhere(['ship_id' => $shipId])
            ->andWhere(['>', 'arrival_dt', $navigation->departure_dt])
            ->andWhere(['<', 'departure_dt', $navigation2->arrival_dt])
            ->all();

        if (!$navigation || !$navigation2) {
            throw new Exception('Навигация не найдена');
        }

        /** @var ShipNavigation $navigate */
        $nav = ' - ';
        foreach ($navigation_between as $navigate) {
            $nav .= "{$navigate->city->title} - ";
        }

        return "{$navigation->city->title}$nav{$navigation2->city->title} ";
    }

    /**
     * @param $shipId
     * @param $navigationId
     * @param $navigationId2
     *
     * @return string
     */
    public function getCity($shipId, $navigationId, $navigationId2)
    {
        /** @var ShipNavigation $navigation */
        $navigation = ShipNavigation::find()
            ->withoutTrashed()
            ->byId($navigationId)

            ->andWhere(['ship_id' => $shipId])
            ->one();

        /** @var ShipNavigation $navigation2 */
        $navigation2 = ShipNavigation::find()
            ->withoutTrashed()
            ->byId($navigationId2)

            ->andWhere(['ship_id' => $shipId])
            ->one();

        return "{$navigation->city->title} {$navigation2->city->title} ";
    }

    /**
     * Получение имени маршрута по идентификатору тура.
     *
     * @param  mixed  $tourId  Идентификатор тура
     *
     * @return string Название в формате (Город-Город-...-Город)
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function getNameByTourId($tourId): string
    {
        $query = new Query();
        $query->from('tour_titles');
        $query->andWhere(['id' => $tourId]);
        $result = $query->one();

        return $result['title'] ?? '';
    }

    /**
     * Получение имени маршрута по идентификатору тура.
     *
     * @param  mixed  $tour  Тур
     *
     * @return string Название в формате (Город-Город-...-Город)
     */
    public function getNameByTour(Tour $tour): string
    {
        return $this->getNameByTourId($tour->id);
    }

    public function getCityByTour(Tour $tour): array
    {
        /** @var array|ShipNavigation[] $navigations */
        $navigations = $this->getNavigationByTour($tour);

        $titles = ArrayHelper::map($navigations, 'id', 'city.title');

        return $titles;
    }

    /**
     * Позволяет по идентификатору тура получить полное имя.
     *
     * @param $shipId
     * @param $navigationId
     *
     * @return int Вывод в минутах
     *
     * @throws Exception
     */
    public function calcFreeTime($shipId, $navigationId): int
    {
        /** @var ShipNavigation $navigation */
        $navigation = ShipNavigation::find()
            ->withoutTrashed()
            ->byId($navigationId)

            ->andWhere(['ship_id' => $shipId])
            ->one();

        if (!$navigation) {
            throw new Exception('Навигация не найдена');
        }

        $date1 = Carbon::parse($navigation->arrival_dt);
        $date2 = Carbon::parse($navigation->departure_dt);

        return $date1->diffInHours($date2);
    }

    /**
     * Дублирует точки навигации в указанном промежутке.
     *
     * @param $shipId - Идентификатор корабля
     * @param $arrivalDT - Дублируемый кусок по дате прибытия
     * @param $departureDT - Дублируемый кусок по дате отплытия
     * @param $offsetDT - От какой точки производить вставку
     * @param bool $save - Сохранить получившийся кусок
     *
     * @throws Exception
     * @throws ValidationException
     */
    public function duplicate($shipId, $arrivalDT, $departureDT, $offsetDT, $save = false): array
    {
        /** @var ShipNavigation[] $navigations */
        $navigations = ShipNavigation::find()
            ->withoutTrashed()

            ->andWhere(['ship_id' => $shipId])
            ->andWhere(['>=', 'arrival_dt', $arrivalDT])
            ->andWhere(['<=', 'departure_dt', $departureDT])
            ->orderBy('arrival_dt')
            ->all();

        if (count($navigations) === 0) {
            throw new Exception('Навигаций в данном промежутке не найдено');
        }

        $offsetDate = Carbon::parse($offsetDT);
        $startDATE = Carbon::parse($navigations[0]->arrival_dt);

        $diff = $offsetDate->diffInMicroseconds($startDATE);

        $newNavigations = [];

        foreach ($navigations as $navigation) {
            $oldArrivalDt = Carbon::parse($navigation->arrival_dt);
            $oldDepartureDt = Carbon::parse($navigation->departure_dt);

            $newArrivalDt = (clone $oldArrivalDt)->addMicroseconds($diff);
            $newDepartureDt = (clone $oldDepartureDt)->addMicroseconds($diff);

            $newNavigations[] = [
                'ship_id' => $navigation->ship_id,
                'city_id' => $navigation->city_id,
                'type_cid' => $navigation->type_cid,
                'old' => ["$oldArrivalDt", "$oldDepartureDt"],
                'new' => ["$newArrivalDt", "$newDepartureDt"],
            ];
        }

        $message = 'Навигация - вычислена!';

        if ($save) {
            foreach ($newNavigations as $navigation) {
                $newShipId = $navigation['ship_id'];
                $newCityId = $navigation['city_id'];
                $newTypeCid = $navigation['type_cid'];
                list($newArrivalDt, $newDepartureDt) = $navigation['new'];
                $this->add($newShipId, $newCityId, $newArrivalDt, $newDepartureDt, $newTypeCid);
            }
            $message = 'Навигация - сохранена!';
        }

        return [
            'message' => $message,
            'data' => $newNavigations,
        ];
    }

    /**
     * @return array|ShipNavigation[]
     */
    public function getNavigationByTour(Tour $tour): array
    {
        /** @var ShipNavigation $navigations */
        $navigationQ = ShipNavigation::find()
            ->withoutTrashed()
            ->joinWith(['city'])
            ->andWhere(['<=', 'arrival_dt', (string)Carbon::parse($tour->departure_dt)->startOfDay()])
            ->andWhere(['>=', 'departure_dt', (string)Carbon::parse($tour->arrival_dt)->startOfDay()])
            ->andWhere(['ship_id' => $tour->ship_id])
            ->orderBy(['arrival_dt' => SORT_ASC]);

        return $navigationQ->all();
    }
}
