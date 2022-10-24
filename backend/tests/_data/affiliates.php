<?php

use tests\generator\HistoryGenerator;

$faker = Faker\Factory::create();

$generator = new HistoryGenerator([
    'actual_address' => function () use($faker){ return $faker->address(); },
    'phone' => function () use($faker){ return $faker->phoneNumber(); },
    'manager_position_title' => function () use($faker){ return $faker->companySuffix(); },
    'manager_full_name' => function () use($faker){ return "$faker->lastName $faker->firstName $faker->monthName"; },
]);

return $generator->generate();