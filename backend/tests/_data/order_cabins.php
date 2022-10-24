<?php

$orderCabins = [];

$query = (new \yii\db\Query())
    ->select("orders.id as order_id, orders.comment as cabin_id")
    ->from('orders');

$items = $query->all();

foreach ($items as $item) {
    $orderCabins[] = [
        "order_id"         => $item["order_id"],
        "cabin_id"         => $item["cabin_id"],
        "virtual_cabin_id" => null,
        "place_id"         => null,

        "created_at"  => \Carbon\Carbon::now(),
    ];
}

return $orderCabins;
