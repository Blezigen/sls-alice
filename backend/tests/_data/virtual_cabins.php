<?php

use common\models\Cabin;
use common\models\CabinPlace;
use common\models\Collection;
use common\models\Tour;
use tests\generator\HistoryGenerator;

$faker = new \Faker\Generator();

$generator = new HistoryGenerator([
    'cabin_id' => Cabin::find(),
    'tour_id' => Tour::find(),
    'number' => function() use($faker){ return $faker->numberBetween(0,10); },
    'gender_type_cid' => Collection::find()->collection('genders'),
    'cabin_place_id' => CabinPlace::find(),
]);

return $generator->generate();