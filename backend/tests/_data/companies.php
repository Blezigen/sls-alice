<?php

use Carbon\Carbon;
use tests\generator\HistoryGenerator;

$faker = Faker\Factory::create();

$generator = new HistoryGenerator([
    'stamp_fid' => null,
    'voucher_stamp_fid' => null,
    'contract_fid' => null,
    'title' => function() use($faker) { return $faker->company(); },
    'legal_address' => function() use($faker) { return $faker->address(); },
    'actual_address' => function() use($faker) { return $faker->address(); },
    'phone' => function() use($faker) { return $faker->phoneNumber(); },
    'email' => function() use($faker) { return $faker->email(); },
    'web' => '?',
    'inn' => function() use($faker) { return $faker->numberBetween(1000000, 9999999); },
    'kpp' => '?',
    'ogrn' => function() use($faker) { return $faker->title; },
    'bik' => '?',
    'account_number' => function() use($faker) { return $faker->numberBetween(1000000, 9999999); },
    'cor_number' => function() use($faker) { return $faker->numberBetween(1000000, 9999999); },
    'director_full_name' => function() use($faker) { return "$faker->lastName $faker->firstName $faker->monthName"; },
    'bookkeeper_full_name' => function() use($faker) { return "$faker->lastName $faker->firstName $faker->monthName"; },
    'okpo' => '?',
    'tour_number' => function() use($faker) { return $faker->numberBetween(1000000, 9999999); },
    'contract_number' => function() use($faker) { return $faker->numberBetween(1000000, 9999999); },
    'contract_start_at' => function() use($faker) { return Carbon::parse($faker->dateTimeBetween('-5 years')); },
    'contract_end_at' => function() use($faker) { return Carbon::parse($faker->dateTimeBetween('now', '+5 years')); },
]);

return $generator->generate(10);