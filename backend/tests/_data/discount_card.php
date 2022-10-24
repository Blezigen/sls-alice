<?php

use common\IConstant;
use common\models\Collection;
use tests\generator\HistoryGenerator;

$faker = Faker\Factory::create();

$generator = new HistoryGenerator([
    'discount_card_type_cid' => Collection::find()->collection(IConstant::COLLECTION_DISCOUNT_CARD_TYPES),
    'value' => function() use($faker) { return $faker->numberBetween(1, 100); },
    'number' => function() use($faker) { return $faker->numberBetween(1, 100); },
]);

return $generator->generate();