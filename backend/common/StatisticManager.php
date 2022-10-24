<?php

namespace common;

use Carbon\Carbon;
use common\exceptions\ValidationException;
use common\models\StatisticAgent;
use common\models\StatisticCity;
use common\models\StatisticContractor;
use common\models\StatisticManagement;
use common\models\StatisticReceipt;
use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\FilterQueryParser;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;

class StatisticManager extends Component
{

    /**
     * @param  array  $filters
     * @param  Carbon|string  $date
     *
     * @return array['plate' => [], 'values' => []]
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ValidationException
     */
    public function generateCityContractor(array $filters = [], Carbon|string $date = 'now'): array{
        $modelClass = StatisticContractor::class;

        $query = $modelClass::find();
        $query->with(['ship', 'contractorType']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

        /** @var StatisticContractor[] $results */
        $results = $query->all();


        $titles = [
            'x' => [],
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->ship_id, $titles['x'])) {
                $titles['x'][$result->ship_id] = [
                    'id' => $result->ship_id,
                    'title' => $result->ship->title,
                    "data" => [],
                ];
            }

            if (!array_key_exists($result->contractor_type_cid, $titles['y'])) {
                $titles['y'][$result->contractor_type_cid] = [
                    'id' => $result->contractor_type_cid,
                    'slug' => $result->contractorType->slug,
                    'title' => $result->contractorType->title,
                    "data" => []
                ];
            }

            $values["{$result->ship_id}:{$result->contractor_type_cid}"] = ArrayHelper::filter($result->attributes, [
                "total_plan",
                "total_fact",
                "total_difference",
                "total_discount",
                "total_commission",
                "count_orders",
                "cabin_count",
                "place_count",
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    public function generateAgentStatistic(array $filters = [], Carbon|string $date = 'now'): array{
        $modelClass = StatisticAgent::class;

        $query = $modelClass::find();
        $query->with(['contractor']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

//        dd($query->createCommand()->rawSql,$query->createCommand()->params);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

        /** @var StatisticContractor[] $results */
        $results = $query->all();

        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->contractor_id, $titles['y'])) {
                $titles['y'][$result->contractor_id] = [
                    "contractor_id" => 19,
                    'contractor_fn' => $result?->contractor?->contractor_fn,
                    "city_id" => $result?->contractor?->city_id,
                    "city" => $result?->contractor?->city?->title,
                    "phone" => $result?->contractor?->phone_1,
                    "email" => $result?->contractor?->email,
                ];
            }

            $values["{$result->contractor_id}"] = ArrayHelper::filter($result->attributes, [
                "cabins_count",
                "place_count",
                "total_price",
                "total_commission",
                "commission",
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    /**
     * @param  array  $filters
     * @param  Carbon|string  $date
     *
     * @return array['plate' => [], 'values' => []]
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     * @throws ValidationException
     */
    public function generateCityStatistic(array $filters = [], Carbon|string $date = 'now'): array
    {
        $modelClass = StatisticCity::class;

        $query = $modelClass::find();
        $query->with(['city', 'ship', 'contractor']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

        /** @var StatisticCity[] $results */
        $results = $query->all();

        $titles = [
            'x' => [],
            'y' => [],
        ];

        $values = [];
        foreach ($results as $result) {
            if (!array_key_exists($result->city_id, $titles['y'])) {
                $titles['y'][$result->city_id] = [
                    'id' => $result->city_id,
                    'title' => $result->city->title,
                    'children' => [],
                    "data" => [],
                ];
            }

            $cityData = &$titles['y'][$result->city_id];

            if (!array_key_exists($result->ship_id, $titles['x'])) {
                $titles['x'][$result->ship_id] = [
                    'id' => $result->ship_id,
                    'title' => $result->ship->title,
                    "data" => [],
                ];
            }

            if (!array_key_exists($result->contractor_id, $cityData['children'])) {
                $cityData['children'][$result->contractor_id] = [
                    'id' => $result->contractor_id,
                    'title' => $result->contractor->contractor_fn,
                    "data" => [
                        'commission_self' => $result->contractor->commission_self,
                        'commission_other' => $result->contractor->commission_other,
                    ]
                ];
            }

            $values["{$result->ship_id}:{$result->city_id}_{$result->contractor_id}"] = ArrayHelper::filter($result->attributes, [
                'total_price',
                'total_payment',
                'total_discount',
                'total_commission',
                'cabin_count',
                'place_count',
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    public function generateManagementStatistic(array $filters = [], Carbon|string $date = 'now')
    {
        /** @var StatisticManagement $modelClass */
        $modelClass = StatisticManagement::class;

        $query = $modelClass::find();
        $query->with(['paymentStatus']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

        /** @var StatisticManagement[] $results */
        $results = $query->all();

        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->order_id, $titles['y'])) {
                $titles['y'][$result->order_id] = [
                    "order_id" => $result->id,
                    'created_at' => $result->created_at,
                    'payment_status' => $result->paymentStatus,
                    'comment' => $result->comment,
                ];
            }

            $values["{$result->order_id}"] = ArrayHelper::filter($result->attributes, [
                "total_plan",
                "total_departure",
                "mis_plan_dep",
                "commission_percent",
                "commission_total",
                "total_payment",
                "mis_tot_pay",
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    public function generateReceiptStatistic(array $filters = [], Carbon|string $date = 'now')
    {
        /** @var StatisticReceipt $modelClass */
        $modelClass = StatisticReceipt::class;

        $query = $modelClass::find();
        $query->with(['status']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

//        dd($query->createCommand()->rawSql, $query->createCommand()->params);

        /** @var StatisticReceipt[] $results */
        $results = $query->all();

//        dd(ArrayHelper::toArray($results));

        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->id, $titles['y'])) {
                $titles['y'][$result->id] = [
                    "payment_id" => $result->id,
                    'payment_dt' => $result->payment_dt,
                    'payment_number' => $result->payment_number,
                    'payment_type' => $result->payment_type,
                    'city_id' => $result->tour->cityStartId,
                    'terminal' => "По умолчанию",
                ];
            }

            $values["{$result->id}"] = ArrayHelper::filter($result->attributes, [
                "payment_amount",
                "description",
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    public function generateEmployStatistic(array $filters = [], Carbon|string $date = 'now')
    {
        /** @var \common\models\StatisticEmploy $modelClass */
        $modelClass = \common\models\StatisticEmploy::class;

        $query = $modelClass::find();
        $query->with(['contractor']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

//        dd($query->createCommand()->rawSql, $query->createCommand()->params);

        /** @var \common\models\StatisticEmploy[] $results */
        $results = $query->all();

//        dd(ArrayHelper::toArray($results));

        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->contractor_id, $titles['y'])) {
                $titles['y'][$result->contractor_id] = [
                    "contractor_id" => $result->contractor_id,
                    'contractor_fn' => $result->contractor->contractor_fn,
                ];
            }

            $values["{$result->contractor_id}"] = ArrayHelper::filter($result->attributes, [
                "order_count_self",
                "order_count_other",
                "total_payment_self",
                "total_payment_other",
            ]);
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

    public function generatePhysicalStatistic(array $filters = [], Carbon|string $date = 'now')
    {
        /** @var \common\models\StatisticPhysical $modelClass */
        $modelClass = \common\models\StatisticPhysical::class;

        $query = $modelClass::find();
        $query->with(['contractor']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

//        dd($query->createCommand()->rawSql, $query->createCommand()->params);

        /** @var \common\models\StatisticPhysical[] $results */
        $results = $query->all();

//        dd(ArrayHelper::toArray($results));

        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->order_id, $titles['y'])) {
                $titles['y'][$result->order_id] = [
                    "order_id" => $result->order_id,
                    "order_number" => $result->order_id,
                ];
            }

            $values["{$result->order_id}"] = [
                "contractor_id" => $result->contractor_id,
                "contractor_fn" => $result->contractor->contractor_fn,
                "city" => $result->contractor->city,
                "phone" => $result->contractor->phone_1,
                "total_price" => $result->total_price,
                "total_payment" => $result->total_payment,
            ];
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }



    public function generateTourStatistic(array $filters = [], Carbon|string $date = 'now')
    {
        /** @var \common\models\StatisticTour $modelClass */
        $modelClass = \common\models\StatisticTour::class;

        $query = $modelClass::find();
        $query->with(['tour.ship']);
        if ($date === 'now'){
            $date = Carbon::now();
        }
        $query->byVersion($date);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $service->requestFilters = $filters;
        $filters = $service->handle($modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($query);
            }
        }

//        dd($query->createCommand()->rawSql, $query->createCommand()->params);

        /** @var \common\models\StatisticTour[] $results */
        $results = $query->all();

//        dd(ArrayHelper::toArray($results));
        $titles = [
            'y' => [],
        ];

        $values = [];

        foreach ($results as $result) {
            if (!array_key_exists($result->tour_id, $titles['y'])) {
                $titles['y'][$result->tour_id] = [
                    "tour_id" => $result->tour_id,
                    "tour_number" => $result->tour_id,
                    "title" => $result->tour->getTitle(),
                    "ship_title" => $result->tour->ship->title,
                    "arrival_dt" => $result->tour->arrival_dt,
                    "departure_dt" => $result->tour->departure_dt,
                ];
            }

            $values["{$result->tour_id}"] = $result->toArray();
        }

        return [
            'titles' => $titles,
            'values' => $values,
        ];
    }

}
