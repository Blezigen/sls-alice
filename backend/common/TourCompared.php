<?php

namespace common;

use Carbon\Carbon;
use common\models\Tour;
use yii\base\Model;

class TourCompared extends Model
{
    private Tour $_compare;
    private Tour $_compared;

    /**
     * @param  Tour  $compare
     * @param  Tour  $compared
     */
    public function __construct(Tour $compare, Tour $compared)
    {
        $this->_compare = $compare;
        $this->_compared = $compared;
        parent::__construct([]);
    }

    public function isParentTour() : bool
    {
        return $this->_compare->parent_tour_id === $this->_compared->id;
    }

    public function isChildTour() : bool
    {
        return $this->_compared->parent_tour_id === $this->_compare->id;
    }

    public function tourDateInRange() : bool
    {
        $pointA1 = Carbon::parse($this->_compared->arrival_dt);
        $pointA2 = Carbon::parse($this->_compare->arrival_dt);
        $pointB1 = Carbon::parse($this->_compared->departure_dt);
        $pointB2 = Carbon::parse($this->_compare->departure_dt);

        return
            min($pointA1,$pointB1) >= min($pointA2,$pointB2) &&
            max($pointA1,$pointB1) <= max($pointA2, $pointB2);
    }

    public function tourDateCrossing(): bool
    {
        $pointComparedA = Carbon::parse($this->_compared->arrival_dt);
        $pointComparedD = Carbon::parse($this->_compared->departure_dt);

        $pointCompareA = Carbon::parse($this->_compare->arrival_dt);
        $pointCompareD = Carbon::parse($this->_compare->departure_dt);

        return
            (
                $pointComparedA >= min($pointCompareA,$pointCompareD) &&
                $pointComparedA <= max($pointCompareA,$pointCompareD)
            ) ||
            (
                $pointComparedD >= min($pointCompareA,$pointCompareD) &&
                $pointComparedD <= max($pointCompareA,$pointCompareD)
            ) ||
            (
                $pointCompareA >= min($pointComparedA,$pointComparedD) &&
                $pointCompareA <= max($pointComparedA,$pointComparedD)
            ) ||
            (
                $pointCompareD >= min($pointComparedA,$pointComparedD) &&
                $pointCompareD <= max($pointComparedA,$pointComparedD)
            );
    }



    public function isEqualShips(): bool
    {
        return $this->_compare->ship_id === $this->_compared->ship_id;
    }

    public function isFullEqual(): bool
    {
        return $this->_compare->id === $this->_compared->id;
    }
}