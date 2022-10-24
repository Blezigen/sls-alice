<?php

namespace tests\fixtures;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use common\models\City;
use common\models\Ship;
use common\models\ShipNavigation;
use common\models\TourCabinPrice;
use tests\modules\HistoryActiveFixture;

class TourCabinPriceFixture extends HistoryActiveFixture
{
    public $modelClass = TourCabinPrice::class;
    public $dataFile = __DIR__ . '/../_data/tour_cabin_price.php';

    public $depends = [TourFixture::class, ShipCabinClassFixture::class];

}
