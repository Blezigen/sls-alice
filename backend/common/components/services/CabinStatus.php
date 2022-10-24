<?php

namespace common\components\services;

use common\contracts\ICabin;
use common\models\Cabin;
use common\models\CabinStatistic;
use common\models\Tour;
use common\models\VirtualCabin;
use common\models\VirtualCabinStatistic;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class CabinStatus extends Model
{
    public ICabin $cabin;

    /** @var Tour[] */
    public array $tours = [];

    /** @var Tour[] */
    public array $selling = [];

    /** @var Tour[] */
    public array $blocked = [];

    /** @var Tour[] */
    public array $reserved = [];

    /** @var Tour[] */
    public array $crossing = [];

    /** @var Tour[] */
    public array $split = [];

    /** @var VirtualCabin[] */
    public array $virtualCabins = [];

    /** @var array[][] */
    public array $problem = [];

    /** @var array[][] */
    public array $hints = [];

    public function formatTours(array $tours): array
    {
        $tourEntities = Tour::find()->select([
            'id',
            'arrival_dt',
            'departure_dt',
//            'title',
            'ship_id'
        ])->andWhere(["IN", "id", array_keys($tours)])->all();
        return array_values(ArrayHelper::map($tourEntities, 'id',
            function (Tour $tour) {
                return [
                    'tour_id'      => $tour->id,
                    'arrival_dt'   => $tour->arrival_dt,
                    'departure_dt' => $tour->departure_dt,
                    'title'        => $tour->title,
                    'ship_id'      => $tour->ship_id,
                ];
            }));
    }

    public function fields()
    {
        return array_merge(parent::fields(), [
            'cabin'    => function () {
                return [
                    'cabin_id' => $this->cabin->id,
                    'number'   => $this->cabin->number,
                ];
            },
            'tours'    => function () {
                return $this->formatTours($this->tours);
            },
            'selling'  => function () {
                return $this->formatTours($this->selling);
            },
            'split'    => function () {
                return $this->formatTours($this->split);
            },
            'blocked'  => function () {
                return $this->formatTours($this->blocked);
            },
            'reserved' => function () {
                return $this->formatTours($this->reserved);
            },
        ]);
    }

    public function __construct(Cabin $cabin)
    {
        $this->cabin = $cabin;
        \Yii::beginProfile("Fill Tours IDS", __METHOD__);
        $this->tours = ArrayHelper::index($cabin->tours, 'id');
        \Yii::endProfile("Fill Tours IDS", __METHOD__);

        $this->process();

        parent::__construct([]);
    }

    public function process(): void
    {
        if ($this->cabin->isVirtual()){
            $statisticQ = VirtualCabinStatistic::find()
                ->byVersion('now')
                ->andWhere(["virtual_cabin_q.id" => $this->cabin->id]);

            /** @var VirtualCabinStatistic[] $statistics */
            $statistics = $statisticQ->all();

            foreach ($statistics as $statistic) {
                $canSelling = true;

                if ($statistic->has_reservation) {
                    $this->reserved[$statistic->tour_id] = $statistic->order_ids;
                    $canSelling = false;
                }

                if ($canSelling)
                    $this->selling[$statistic->tour_id] = [];
            }

            return;
        }

        $statisticQ = CabinStatistic::find()
            ->byVersion('now')
            ->andWhere(["cabin_q.id" => $this->cabin->id]);

//        dd($statisticQ->createCommand()->rawSql);

        /** @var CabinStatistic[] $statistics */
        $statistics = $statisticQ->all();

        foreach ($statistics as $statistic) {
            $canSelling = true;
            if ($statistic->has_tour_block || $statistic->has_system_block) {
                $this->blocked[$statistic->tour_id] = [];
                $canSelling = false;
            }

            if (!empty($statistic->crossing_tour_ids)) {
                $result = array_filter(
                    $statistic->crossing_tour_ids,
                    function ($data) use ($statistic) {
                        return "$data" !== "{$statistic->tour_id}";
                    });

                if (!empty($result)) {
                    $this->crossing[$statistic->tour_id] = $result;
                    $canSelling = false;
                }
            }

            if ($statistic->has_reservation) {
                $this->reserved[$statistic->tour_id] = $statistic->order_ids;
                $canSelling = false;
            }

            if ($statistic->has_virtual_in_tour) {
                $this->virtualCabins[$statistic->tour_id] = $statistic->virtual_cabin_ids;
                $this->split[$statistic->tour_id]
                    = $statistic->virtual_cabin_ids;
                $canSelling = false;
            }

            if ($canSelling)
                $this->selling[$statistic->tour_id] = [];
        }
    }

    public function hasProblem(): bool
    {
        return !empty($this->problem);
    }

    /**
     * Каюта в туре находится в статусе - зарезервирована
     */
    public function cabinReservedInTour(Tour $compare): bool
    {
        return array_key_exists($compare->id, $this->reserved);
    }

    /**
     * Каюта в туре находится в статусе - заблокирована
     */
    public function cabinBlockedInTour(Tour $compare): bool
    {
        return array_key_exists($compare->id, $this->blocked);
    }

    /**
     * Каюта в туре находится в статусе - разделена
     */
    public function cabinSplitInTour(Tour $compare): bool
    {
        return array_key_exists($compare->id, $this->split);
    }

    /**
     * Каюта в туре находится в статусе - продажа
     */
    public function cabinSellingInTour(Tour $compare): bool
    {
        return array_key_exists($compare->id, $this->selling);
    }

    public function checkProblem()
    {
        if (!empty($this->crossing)) {
            $this->addProblem('tour_cabin_crossing',
                'Обнаружено множественное использование каюты {cabin_id} в турах {tour_ids}',
                [
                    'cabin_id' => $this->cabin->id,
                    'tour_ids' => implode(",", array_keys($this->crossing)),
                    'advance'  => $this->crossing,
                ]);
        }
    }

    public function addProblem(
        string $slug,
        string $message,
        array $options = []
    ): void {
        $this->problem[$slug][] = [
            'message' => \Yii::t('app', $message, $options),
            'options' => $options,
        ];
    }

    private function addHint(
        string $slug,
        string $message,
        array $options = []
    ): void {
        $this->hints[$slug][] = [
            'message' => \Yii::t('app', $message, $options),
            'options' => $options,
        ];
    }

    public function cabinHasVirtualInTour(Tour $tour)
    {
        return array_key_exists($tour->id, $this->split);
    }
}
