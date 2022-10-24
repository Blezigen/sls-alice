<?php

namespace tests\fixtures;

use common\models\OrderCabin;
use tests\modules\HistoryActiveFixture;

class OrderCabinFixture extends HistoryActiveFixture
{
    public $modelClass = OrderCabin::class;
    public $dataFile = __DIR__ . '/../_data/order_cabins.php';

    public $depends = [OrderFixture::class, CabinFixture::class];
}
