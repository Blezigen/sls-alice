<?php

namespace common\events;

use common\models\Cabin;
use common\models\Ship;
use common\models\Tour;
use yii\base\Event;

class OrderCabinFreeEvent extends Event
{
    public Cabin $cabin;
    public Ship|null $ship = null;
    public Tour|null $tour = null;
}