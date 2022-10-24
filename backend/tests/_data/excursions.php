<?php

use common\models\City;
use Faker\Factory;
use tests\generator\HistoryGenerator;

$faker = Factory::create();

$generator = new HistoryGenerator([
    'city_id' => City::find(),
    'title' => function() use($faker) { return $faker->title(); },
    'description' => function() use($faker) { return $faker->text(); },
    function() use($faker) {
        $price = $faker->numberBetween(100, 1000) * 100;
        return [
            'price' => $price,
            'price_child' => $price / 2
        ];
    },
    'duration' => function() use($faker){ return $faker->numberBetween(5, 100); },
    'from_year' => 18,
    'to_year' => 100,
    'from_year_child' => 6,
    'to_year_child' => 18,
    'recommendation_for_child' => function () use($faker) { return $faker->boolean(); },
]);

return $generator->generate(100);