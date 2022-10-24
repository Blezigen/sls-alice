<?php
$faker = Faker\Factory::create();

$cabinClasses = [];

$query = (new \yii\db\Query())
    ->select("ships.id as ship_id")
    ->from('ships');


$items = $query->all();

foreach ($items as $item) {

    $classNumbers = $faker->numberBetween(4,8);

    for ($i = 0; $i < $classNumbers; $i++) {
        $number = $faker->numberBetween(1000, 9999);
        $cabinClasses[] = [
            'ship_id'            => $item["ship_id"],
            'title'              => "Класс каюты №{$number} для корабля с ID:"
                .$item["ship_id"],
            'max_place_base'     => $faker->numberBetween(1, 3),
            'max_place_advance'  => $faker->numberBetween(0, 2),
            'k_calculation'      => 1,
            'k_sales'            => 1,
            'can_search'         => true,
            'disable_commission' => false,
            'description'        => "Описание",
        ];
    }
}


return $cabinClasses;