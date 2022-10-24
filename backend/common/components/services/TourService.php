<?php

namespace common\components\services;

use api\exceptions\NotFoundHttpException;
use api\modules\tour_module\models\FoodStatisticGenerator;
use api\modules\tour_module\models\OrderStatisticGenerator;
use api\modules\tour_module\models\SettingConstant;
use Carbon\Carbon;
use common\exceptions\NotImplementException;
use common\models\Excursion;
use common\models\ShipNavigation;
use common\models\Tour;
use common\models\TourExcursionInfo;
use Yii;

class TourService
{
    private NavigationService $navigationService;

    public function __construct(NavigationService $navigationService)
    {
        $this->navigationService = $navigationService;
    }

    public function lostCabinDays()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::LOST_CABIN_DAYS,
            3
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getAvailableExcursion(int $id): array
    {
        /** @var Tour $tour */
        $tour = Tour::find()
            ->byId($id)
            ->with('ship.shipNavigations')
            ->one();

        if($tour == null){
            throw new NotFoundHttpException("Tour($id) not found");
        }

        $shipNavigations = $tour->ship->shipNavigations;

        $shipNavigationTimes = [];
        $shipNavigationByCities = [];
        foreach ($shipNavigations as $shipNavigation) {
            $shipNavigationByCities[$shipNavigation->city_id][] = $shipNavigation;
            $shipNavigationTimes[$shipNavigation->id] = $this->navigationService
                ->calcFreeTime($shipNavigation->ship_id, $shipNavigation->id);
        }

        $availableShipNavigation = [];
        foreach ($shipNavigationByCities as $shipNavigations) {
            $availableShipNavigation[] = array_reduce($shipNavigations, function($current, $item) use($shipNavigationTimes) {
                if ($current == null){
                    return $item;
                }

                if($shipNavigationTimes[$current->id] < $shipNavigationTimes[$item->id]) {
                    return $item;
                }

                return $current;
            }, null);
        }

        $excursions = [];
        foreach ($availableShipNavigation as $shipNavigation){
            $excursions = array_merge(
                $excursions,
                TourExcursionInfo::find()
                    ->addSelect([
                        'has_program' => 'tour_excursions.has_program',
                        'has_sell' => 'tour_excursions.has_sell',
                        'excursions.*'
                    ])
                    ->leftJoin('tour_excursions', 'tour_excursions.excursion_id = excursions.id')
                    ->andWhere([
                        'or',
                        ['is', 'tour_excursions.id', null],
                        ['tour_excursions.tour_id' => $id]
                    ])
                    ->andWhere(['city_id' => $shipNavigation->city_id])
                    ->andWhere(['<=', 'duration', $shipNavigationTimes[$shipNavigation->id]])
                    ->all()
            );
        }

        return $excursions;
    }

    public function getFoodStatistic(Tour $tour): string
    {
        $generator = new FoodStatisticGenerator($tour);
        return $generator->generate("@runtime/temp/report/");
    }

    public function getOrderStatistic(Tour $tour): string
    {
        $generator = new OrderStatisticGenerator($tour);
        return $generator->generate("@runtime/temp/report/");
    }
}