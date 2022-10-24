<?php

use Carbon\Carbon;
use common\models\Order;
use tests\generator\HistoryGenerator;

$faker = new \Faker\Generator();

$orders = Order::find()->select(["id"])->all();
$result = [];
foreach ($orders as $order) {
    $generator = new HistoryGenerator([
        "type_cid"       => null,
        "order_id"       => $order->id,
        "status_cid"     => \common\models\Collection::find()
            ->collection(\common\IConstant::COLLECTION_ORDER_PAYMENT_STATUS)
            ->andWhere([
                "IN",
                "slug",
                [
                    \common\IConstant::ORDER_PAYMENT_STATUS_INIT,
                    \common\IConstant::ORDER_PAYMENT_STATUS_DEPOSIT,
                    \common\IConstant::ORDER_PAYMENT_STATUS_DENIED,
                ]
            ]),
        "payment_amount" => function () use ($faker) {
            return $faker->numberBetween(1000, 10000);
        },
        "payment_dt"     => function () {
            return Carbon::now()->format("Y-m-d H:i:s");
        },
        "payment_type"   => "default",
        "description"    => null,
    ]);
    $result = array_merge($result, $generator->generate(random_int(0, 2)));
}

return $result;