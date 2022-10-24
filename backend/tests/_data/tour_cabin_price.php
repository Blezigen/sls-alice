<?php
$listItemQ = (new \yii\db\Query())
    ->select(
       "tours.id as tour_id, tours.ship_id, ship_cabin_classes.id AS class_id"
    )
    ->from("tours")
    ->innerJoin("ship_cabin_classes", "ship_cabin_classes.ship_id = tours.ship_id");

//dd($listItemQ->createCommand()->rawSql);

$listItems = $listItemQ->all();

$data = [];

$faker = new Faker\Generator();

foreach ($listItems as $listItem){
    $data[] = [
        'tour_id'             => $listItem["tour_id"],
        'ship_cabin_class_id' => $listItem["class_id"],
        'price'               => $faker->numberBetween(1000, 10000),
        'additional_price'    => $faker->numberBetween(1000, 5000),
        'increasing_percent'  => 0,
        'is_use_discount'     => $faker->numberBetween(0, 1) === 1,

        "created_at"  => \Carbon\Carbon::now(),
    ];
}

return $data;