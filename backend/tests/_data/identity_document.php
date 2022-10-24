<?php

use Carbon\Carbon;
use common\IConstant;
use common\models\Collection;
use tests\generator\HistoryGenerator;

$faker = \Faker\Factory::create();

$generator = new HistoryGenerator([
    'type_cid' => Collection::find()->collection(IConstant::COLLECTION_IDENTITY_DOCUMENT_TYPES),
    'gender_cid' => Collection::find()->collection(IConstant::COLLECTION_GENDERS),
    'scan_fid' => null,
    'last_name' => function() use($faker) { return $faker->lastName; },
    'first_name' => function() use($faker) { return $faker->firstName; },
    'third_name' => function() use($faker) { return $faker->lastName; },
    'birth_date' => Carbon::now()->subYears(65),
    'serial' => function() use($faker) { return $faker->numberBetween(1000, 9999); },
    'number' => function() use($faker) { return $faker->numberBetween(100000, 999999); },
    'issue_date' => null,
    'issued' => 'ОТД. УФМС РФ по РТ',
    'issue_code' => null,
    'phone' => null,
    'email' => null,
    'city' => null,
    'post_code' => null,
    'address' => null,
]);

return $generator->generate();
