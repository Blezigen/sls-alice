<?php

$orderPlaces = [];

$query = (new \yii\db\Query())
    ->select(
        "orders.id as order_id,".
        "order_cabins.id as order_cabin_id,".
        "cabins.id as cabin_id,".
        "ship_cabin_classes.max_place_base,".
        "ship_cabin_classes.max_place_advance"
    )
    ->from('orders')
    ->innerJoin("order_cabins", "order_cabins.order_id = orders.id")
    ->innerJoin("cabins", "order_cabins.cabin_id = cabins.id")
    ->innerJoin("ship_cabin_classes",
        "ship_cabin_classes.id = cabins.ship_cabin_class_id");


$items = $query->all();

foreach ($items as $item) {
    for ($i = 0; $i < $item["max_place_base"]; $i++) {
        $orderPlaces[] = [
            'order_cabin_id'                => $item["order_cabin_id"],
            'cabin_place_id'                => null,
            'insurance_cid'                 => null,
            'identity_document_id'          => 1,
            'travel_package_cid'            => null,
            'discount_card_id'              => null,
            'gender_cid'                    => [
                \common\IConstant::COLLECTION_GENDERS => \common\IConstant::GENDER_MAN
            ],
            'discount_category_default_id'  => null,
            'discount_category_early_id'    => null,
            'discount_category_constant_id' => null,
            'discount_category_online_id'   => null,
            'total_price'                   => null,
            'total_changed_price'           => null,
            'place_type_cid'                => [
                \common\IConstant::COLLECTION_PLACE_TYPES => \common\IConstant::PLACE_TYPE_BASE
            ],
        ];
    }

    for ($i = 0; $i < $item["max_place_advance"]; $i++) {
        $orderPlaces[] = [
            'order_cabin_id'                => $item["order_cabin_id"],
            'cabin_place_id'                => null,
            'insurance_cid'                 => null,
            'identity_document_id'          => 2,
            'travel_package_cid'            => null,
            'discount_card_id'              => null,
            'gender_cid'                    => [
                \common\IConstant::COLLECTION_GENDERS => \common\IConstant::GENDER_WOMEN
            ],
            'discount_category_default_id'  => null,
            'discount_category_early_id'    => null,
            'discount_category_constant_id' => null,
            'discount_category_online_id'   => null,
            'total_price'                   => null,
            'total_changed_price'           => null,
            'place_type_cid'                => [
                \common\IConstant::COLLECTION_PLACE_TYPES => \common\IConstant::PLACE_TYPE_ADVANCE
            ],
        ];
    }
}


return $orderPlaces;
