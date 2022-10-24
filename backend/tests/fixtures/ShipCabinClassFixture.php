<?php

namespace tests\fixtures;

use common\models\ShipCabinClass;
use tests\modules\HistoryActiveFixture;

class ShipCabinClassFixture extends HistoryActiveFixture
{
    public $modelClass = ShipCabinClass::class;
    public $dataFile = __DIR__ . '/../_data/cabinClasses.php';
    public $depends = [ShipFixture::class, CollectionFixture::class];

}
