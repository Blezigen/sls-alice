<?php

namespace tests\fixtures;

use common\models\Order;
use common\models\OrderPayment;
use tests\modules\ActiveFixture;

class OrderPaymentFixture extends ActiveFixture
{
    public $modelClass = OrderPayment::class;
    public $dataFile = __DIR__ . '/../_data/order_payments.php';
    public $depends = [OrderFixture::class, CollectionFixture::class];
}
