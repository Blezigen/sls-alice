<?php

namespace common\services;

use common\behaviors\query\HistoryBehaviors;
use common\models\Ship;
use yii\base\Model;

class ShipServices extends Model
{
    public function make($title)
    {
        return new Ship([
            'title' => $title,
        ]);
    }

    /**
     * @return \common\queries\ShipQuery|HistoryBehaviors
     */
    public function getQuery()
    {
        return Ship::find();
    }

    /**
     * @param $id
     *
     * @return \yii\db\ActiveRecord|Ship|null
     */
    public function getActualById($id)
    {
        return $this->getQuery()->byId($id)->one();
    }

    /**
     * Позволяет определить использование корабля в активном туре
     *
     * @param $shipId
     *
     * @return array|bool
     */
    public function validateActiveTourWhereShipInclude($shipId)
    {
        // todo: Необходимо реализовать
        $errors = [];
        $tours = [1];
        foreach ($tours as $tour) {
            $errors[] = [
                'message' => "Используется в туре, 'Название тура'",
                'tour_id' => $tour,
            ];
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    public function enableShip($id)
    {
        $current = $this->getActualById($id);
        $current->active();

        return $current;
    }

    public function disableShip($id, $verbose = false)
    {
        $current = $this->getActualById($id);

        if (!$verbose) {
            $this->validateActiveTourWhereShipInclude($id);
        }

        $current->disable();

        return $current;
    }
}
