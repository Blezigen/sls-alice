<?php

namespace common;

use api\exceptions\NotFoundHttpException;
use Carbon\Carbon;
use common\exceptions\NotImplementException;
use common\exceptions\ReportServiceException;
use common\models\Collection;

use common\models\PriceSchedule;
use common\models\PriceScheduleClass;
use common\models\PriceScheduleTour;
use common\models\PriceScheduleValue;
use common\models\Ship;
use common\models\ShipCabinClass;
use common\models\Tour;
use common\queries\CabinQuery;
use common\queries\ShipAdvancedValueQuery;
use common\queries\ShipCabinClassQuery;
use common\queries\ShipQuery;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Redbox\PersonalSettings\exceptions\ValidationException;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ReportService
{

    public $reportSavePath = "@runtime/temp/report/";

    /**
     * Расчёт цены суток теплохода
     *
     * {Цена_суток_теплохода} = {Теплоход:(план по доходам)} / {Теплоход:(кол-во дней в навигации)}
     *
     * <a href='http://git.rdbx24.ru/germes/germes.river.backend/-/issues/47'>Issue #47</a>
     *
     * @throws NotInstantiableException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function getPriceOfDateOnTheShip(Ship $ship, $year = null)
    {
        $navigationLength = $ship->getNavigationLength($year);
        $plan = $ship->getPlan($year);

        if ($navigationLength === 0) {
            throw new ReportServiceException(Yii::t('app', 'Не указан параметр навигационной длины в днях (navigation_length) у корабля ID:{ship_id}', [
                "ship_id" => $ship->id,
                "ship_title" => $ship->title,
                "ship_short_title" => $ship->short_title,
            ]), 404);
        }

        return $plan / $navigationLength;
    }

    /**
     * Расчёт цены идеальных суток для теплохода
     *
     * {Цена_идеальных_суток} = {Цена_суток_теплохода} x {Системный коэффициент:(К)}
     *
     * <a href='http://git.rdbx24.ru/germes/germes.river.backend/-/issues/47'>Issue #47</a>
     *
     * @param  Ship  $ship
     * @param  null  $year
     *
     * @return float|int
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ValidationException
     */
    public function getPerfectPriceOfDateOnTheShip(Ship $ship, $year = null) : float|int
    {
        $K = Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::K,
            0
        );


        if ($K === 0) {
            throw new \Exception(Yii::t('app', 'K is equal 0'));
        }

        return round($this->getPriceOfDateOnTheShip($ship, $year) / $K, 2);
    }

    /**
     * Расчёт цены идеальных суток по туру
     *
     * {Цена_идеальных_суток_по_туру}</a> = {Цена_суток_теплохода} x {Системный коэффициент:({Тур:(название коэффициента)})}
     *
     * @param $tourId
     * @param $year
     *
     * @return float|int
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ValidationException
     *
     * @see \common\ReportService::getPriceOfDateOnTheShip()
     * <a href='http://git.rdbx24.ru/germes/germes.river.backend/-/issues/47'>Issue #47</a>
     */
    public function getPerfectPriceOfDateOnTheTour(Tour $tour, $year = null)
    {
        $priceOfDateOnTheShip = $this->getPriceOfDateOnTheShip($tour->ship,$year);

        switch ($tour->coefficient_name) {
            case "K1":
                $coefficientName = SettingConstant::K1;
                break;
            case "K2":
                $coefficientName = SettingConstant::K2;
                break;
            case "K3":
                $coefficientName = SettingConstant::K3;
                break;
            case "K":
                $coefficientName = SettingConstant::K;
                break;
            default:
                throw new \Exception("Не удалось определить коэффициент для расчёта у тура: {$tour->id}");
        }

        $k = Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            $coefficientName,
            1
        );

        return round($priceOfDateOnTheShip * $k,2);
    }

    /**
     * Расчёт цены по классу кают
     *
     * {цена_по_классу_каюты} = {Цена_идеальных_суток_по_туру} x ({кол-во суток} - 1) x {Класс каюты:(Коэф.расчёта)} / ({Класс каюты:(Кол-во мест)} - {КОЛ.ВО_БРИГАДНЫХ_КАЮТ})
     *
     * @param $tourId
     * @param $cabinClassId
     * @param $dayCount
     * @param  null  $year
     *
     * @return float
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ValidationException
     * @throws NotImplementException
     */
    public function getPriceByCabinClass(
        Tour $tour,
        ShipCabinClass $cabinClass,
        $dayCount,
        $year = null
    ) {
        $perfectPriceOnTheTour = $this->getPerfectPriceOfDateOnTheTour($tour, $year);
        $kCalculation = floatval($cabinClass->k_calculation);
        $kPlaceCount = $cabinClass->max_place_base;

        if ($kPlaceCount <= 0) {
            throw new \Exception(Yii::t('app', 'Cabin class count={count}',
                ['count' => $kPlaceCount]));
        }

        $countCrewCabins = $cabinClass->ship->getCountCrewCabins();

        $result = $perfectPriceOnTheTour * ($dayCount - 1) *
            (($kCalculation / $kPlaceCount) - $countCrewCabins);

//        dd($perfectPriceOnTheTour, $dayCount-1, $kCalculation, $kPlaceCount, $countCrewCabins, $result);

        return $result;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getTotalPrice(int $tourId): float
    {
//        $sumReservationWithCommission = Order::find()
//            ->withoutTrashed()
//            
//            ->joinWith('orderCabins', function(OrderCabinQuery $orderCabinQuery){
//                $orderCabinQuery->withoutTrashed();
//                $orderCabinQuery;
//            })
//            ->joinWith('orderCabins.orderPlaces', function(OrderPlaceQuery $orderPlaceQuery){
//                $orderPlaceQuery->withoutTrashed();
//                $orderPlaceQuery;
//            })
//            ->sum('order_places.total_price') - Order::find()
//                ->withoutTrashed()
//                
//                ->sum('_commission');
        /** @var Tour $tour */
        $tour = Tour::find()
            ->withoutTrashed()
            ->byId($tourId)
            ->one() ??
            throw new NotFoundHttpException("Tour ($tourId) not found");

        $sumReservationWithCommission = $tour
            ->getOrders()
            ->withoutTrashed()
            ->sum('_total_price - _commission');

        $collection = Collection::find()
            ->slug(IConstant::ORDER_TYPE_TEMP)
            ->collection(IConstant::COLLECTION_ORDER_TYPE)
            ->one()
            ?? throw new NotFoundHttpException("Collection (slug = "
                .IConstant::ORDER_TYPE_TEMP.", collection = "
                .IConstant::COLLECTION_ORDER_TYPE.") not found");
        $sumTemporalReservation = $tour
            ->getOrders()
            ->withoutTrashed()
            ->andWhere(['status_cid' => $collection->id])
            ->sum('_total_price');

        $tour->ship
            ->getCabins()
            ->withoutTrashed()
            ->andWhere(['in', '', '']);
        $sumFreeReservation = $tour
            ->getOrders()
//            ->getTourCabinPrices()
            ->withoutTrashed()
            ->sum();

        $coefficient = 0.9;

        return $sumReservationWithCommission + (($sumFreeReservation
                    + $sumTemporalReservation) * $coefficient);
    }

    public function getBasicCalculateTableDataFromShip(Ship $ship)
    {
        $shipBeautifulDayPrice = $this->getPerfectPriceOfDateOnTheShip($ship);

        /** @var Tour[] $tours */
        $tours = Tour::find()
            ->shipId($ship->id)
            ->select(["id", "ship_id", "coefficient_name"])
            ->with([
                'ship' => function(ShipQuery $q){
                    $q->select(["id"]);
                },
                'ship.cabins' => function(CabinQuery $q){
                    $q->select(["id", 'ship_id', 'ship_cabin_class_id', "id"]);
                },
                'ship.shipAdvancedValues' => function(ShipAdvancedValueQuery $q){
                    $q->select(["id", 'ship_id', 'slug', "value"]);
                },
                'ship.crewCabins' => function($q){
                    $q->select(new Expression("1"));
                },
                'ship.shipCabinClasses' => function(ShipCabinClassQuery $q){
                    $q->select(["id", "k_calculation","max_place_base","ship_id"]);
                },
                'ship.shipCabinClasses.ship' => function(ShipQuery $q){
                    $q->select(["id"]);
                },
                'ship.shipCabinClasses.ship.cabins' => function(CabinQuery $q){
                    $q->select(["id", 'ship_id', 'ship_cabin_class_id', "id"]);
                }
            ])->all();


        $schedule = new PriceSchedule([
            "start_dt" => Carbon::now()->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            "ship" => $ship,
            "beautiful_day_price" => $this->getPerfectPriceOfDateOnTheShip($ship),
        ]);

        $priceScheduleClass = [];
        $priceScheduleTours = [];
        $priceScheduleValues = [];

        foreach ($tours as $tour){
            $shipBeautifulDayPrice = $this->getPerfectPriceOfDateOnTheTour($tour);
            $priceScheduleTours[] = new PriceScheduleTour([
//                "tour" => $tour,
                "tour_id" => $tour->id,
                "beautiful_day_price" => $shipBeautifulDayPrice,
            ]);

            foreach ($ship->shipCabinClasses as $cabinClass){
                if (!array_key_exists($cabinClass->id, $priceScheduleClass)) {
                    $priceScheduleClass[] = new PriceScheduleClass([
//                        "cabinClass" => $cabinClass->id,
                        "cabin_class_id" => $cabinClass->id,
                        "coefficient" => floatval($cabinClass->k_calculation),
                        "changed_coefficient" => null
                    ]);
                }
                $calculatedPrice = $this->getPriceByCabinClass($tour, $cabinClass, $tour->dayCount);
                $priceScheduleValues[] = new PriceScheduleValue([
//                    "cabinClass" => $cabinClass->id,
                    "cabin_class_id" => $cabinClass->id,
//                    "tour" => $tour->id,
                    "tour_id" => $tour->id,
                    "price" => $calculatedPrice,
                    "changed_price" => null,
                ]);

            }
        }

        $schedule->priceScheduleClasses = array_values($priceScheduleClass);
        $schedule->priceScheduleTours = array_values($priceScheduleTours);
        $schedule->priceScheduleValues = array_values($priceScheduleValues);

        return $schedule;
    }

    public function formingCalculationResultExcel($calcData)
    {
        if (array_key_exists("price_schedule_classes", $calcData)){
            $calcData["priceScheduleClasses"] = array_map(function ($data){
                return new PriceScheduleClass($data);
            }, $calcData["price_schedule_classes"]??[]);
            unset($calcData["price_schedule_classes"]);
        }
        if (array_key_exists("price_schedule_tours", $calcData)){
            $calcData["priceScheduleTours"] = array_map(function ($data){
                return new PriceScheduleTour($data);
            }, $calcData["price_schedule_tours"]??[]);

            unset($calcData["price_schedule_tours"]);
        }
        if (array_key_exists("price_schedule_values", $calcData)){
            $calcData["priceScheduleValues"] = array_map(function ($data){
                return new PriceScheduleValue($data);
            }, $calcData["price_schedule_values"]??[]);
            unset($calcData["price_schedule_values"]);
        }

        $schedule = new PriceSchedule($calcData);

        $dateFormat = 'd.m.Y H:i:s';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Дата применения');
        $sheet->setCellValue('A2', 'Теплоход');

        $sheet->setCellValue('B1', Carbon::parse($schedule->start_dt)->format($dateFormat));
        $sheet->setCellValue('B2', $schedule->ship->title);

        $sheet->setCellValue('A3', 'Дата');
        $sheet->setCellValue('B3', 'Тур');
        $sheet->setCellValue('C3', 'Длительность');
        $sheet->setCellValue('D3', 'Сумма');
        $sheet->setCellValue('E3', 'БГ');
        $sheet->setCellValue('F3', 'Класс каюты');
        $sheet->setCellValue('G3', 'Коэффициент');
        $sheet->setCellValue('H3', 'ИЗМ. Коэффициент');
        $sheet->setCellValue('I3', 'Цена');
        $sheet->setCellValue('J3', 'ИЗМ. Цена');

        $values = ArrayHelper::map($schedule->priceScheduleValues, function ($data){
            return "{$data->tour_id}:{$data->cabin_class_id}";
        }, function ($data){
            return $data;
        });

        $row = 4;
        foreach ($schedule->priceScheduleTours as $tour){
            foreach ($schedule->priceScheduleClasses as $class){
                $sheet->setCellValue("A{$row}", Carbon::parse($tour->tour->departure_dt)->format($dateFormat));
                $sheet->setCellValue("B{$row}", "({$tour->tour->id})".$tour->tour->getTitle());
                $sheet->setCellValue("C{$row}", $tour->tour->dayCount);
                $sheet->setCellValue("D{$row}", $tour->beautiful_day_price);
                $sheet->setCellValue("E{$row}", count($tour->tour->crewCabins));
                $sheet->setCellValue("F{$row}", $class->cabinClass->title);
                $sheet->setCellValue("G{$row}", $class->coefficient);
                $sheet->setCellValue("H{$row}", $class->changed_coefficient);
                $sheet->setCellValue("I{$row}", ($values["{$tour->tour_id}:{$class->cabin_class_id}"])?->price ?? 0);
                $sheet->setCellValue("J{$row}", ($values["{$tour->tour_id}:{$class->cabin_class_id}"])?->changed_price ?? 0);

                $row++;
            }
        }

        $writer = new Xlsx($spreadsheet);

        $fileNameDate = Carbon::now()->format('YmdHis');

        $fileName = Yii::getAlias($this->reportSavePath.$fileNameDate.'.xlsx');

        $writer->save($fileName);


        return [
            "file" => Url::toRoute(["/tool/report/file", "file"=>$fileNameDate], true)
        ];
    }
}
