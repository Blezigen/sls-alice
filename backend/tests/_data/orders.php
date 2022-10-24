<?php

$orders = [];

$query = (new \yii\db\Query())
    ->select(
        "tours.id as tour_id,".
        "cabins.id as cabin_id"
    )
    ->from('tours')
    ->innerJoin("ships", "ships.id = tours.ship_id")
    ->innerJoin("cabins", "ships.id = cabins.ship_id");

$items = $query->all();

$contractors = \common\models\Contractor::find()->all();

foreach ($items as $item){
    $orders[] =     [
        'order_type_cid' => [
            \common\IConstant::COLLECTION_ORDER_TYPE => \common\IConstant::ORDER_TYPE_GENERAL,
        ],

        'manager_uid' => null,
        'tour_id' => $item["tour_id"],
        'contractor_id' => random_int(0, count($contractors)-1),
        'reserve_end_date' => null,
        'commission_delta' => null,
        'comment' => $item["cabin_id"]."",
        'send_comment_to_director' => null,
        'ignore_payment' => null,
        'ignore_report' => null,

        '_total_plan' => null,
        '_total_departure' => null,
        '_total_without_commission' => null,
        '_total_discount_amount' => null,
        '_total_price' => null,
        '_total_fact' => null,
        '_commission_agent' => null,
        '_commission' => null,

        'created_at' => \Carbon\Carbon::now(),
    ];
}


return $orders;