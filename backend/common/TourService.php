<?php

namespace common;

use common\models\Tour;
use yii\base\Exception;

class TourService
{
    /**
     * @param $tourId
     *
     * @return Tour
     *
     * @throws Exception
     */
    public function findTour($tourId)
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
        /** @var Tour $tour */
        $tour = $this->findTour($tourId);

        if (!$tour) {
            throw new Exception('Тур не найден');
        }

        return $tour;
    }
}
