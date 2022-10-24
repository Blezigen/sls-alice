<?php

namespace common;

use Carbon\Carbon;
use common\models\Discount;
use common\models\OrderPlace;
use yii\helpers\ArrayHelper;

class DiscountService
{
    private bool $isLoaded = false;

    /** @var array|Discount[] */
    public array $discounts = [];

    /**
     * @param  Carbon|\DateTime|string  $byDate
     *
     * @return array|Discount[]
     */
    public function load(Carbon|\DateTime|string $byDate = "actual"): array
    {
        if (!$this->isLoaded) {
            $this->discounts = Discount::find()
                ->with('discountType')
                ->byVersion($byDate)
                ->all();
        }
        return $this->discounts;
    }

    /**
     * @param  string|null  $discountTypeSlug
     *
     * @return array|Discount
     */
    public function getDiscounts(string $discountTypeSlug = null, string $discountCauseType = NULL): array
    {
        $results = array_filter($this->discounts,
            function ($value) use ($discountTypeSlug) {
                if ($discountTypeSlug !== null){
                    if ($value instanceof Discount) {
                        if ($value->eqType($discountTypeSlug)) {
                            return true;
                        }
                    }
                    return false;
                }
                return true;
            });

        $results = array_filter($results,
            function ($value) use ($discountCauseType) {
                if ($discountCauseType !== null){
                    if ($value instanceof Discount) {
                        if ($value->eqCauseType($discountCauseType)) {
                            return true;
                        }
                    }
                    return false;
                }
                return true;
            });

        return $results;
    }

    /**
     * @param  Discount  $discount
     * @param  OrderPlace  $place
     *
     * @return bool
     */
    public function canUseDiscount(Discount $discount, OrderPlace $place): bool
    {
        switch ($discount->discountCauseType->slug){
            case IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_1: // Действует на любые 3 заказа.
                //todo: Определить возможность назначения этой скидки
                return false;
                break;
            case IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_2: // Система смотрит на переменные возраста для пенсионера
                return $place->identityDocument->isPensioner();
            case IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_3: // Система смотрит на переменные возраста для ребёнка
                return $place->identityDocument->isChild();
            case IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_4: // Система смотрит на документ, который подтверждает день рождения.
                return $place->identityDocument->todayIsBirthDate();
            case IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_5: // Смотрит на параметры скидки, отображается в случае если соблюдены правила.
                $order = $place->orderCabin->order;
                $tour = $order->tour;
                $days = $discount->summary["days"] ?? 30;
                $orderReserveDT = Carbon::parse($order->created_at)->startOfDay();
                $tourDepartureDT = Carbon::parse($tour->departure_dt)->subDays($days)->startOfDay();

                if ($tourDepartureDT->lessThanOrEqualTo($orderReserveDT)){
                    return true;
                } else {
                    return false;
                }

                break;
        }
        return true;
    }
}