<?php

namespace tests\fixtures;

use common\models\Tour;
use tests\modules\HistoryActiveFixture;

class TourFixture extends HistoryActiveFixture
{
    public $modelClass = Tour::class;
    public $dataFile = __DIR__ . '/../_data/tours.php';

    public $depends = [
        ShipFixture::class,
        ShipNavigationFixture::class
    ];
}
