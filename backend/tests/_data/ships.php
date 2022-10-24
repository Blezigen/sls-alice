<?php

use common\IConstant;
use common\models\Collection;
use common\models\Company;
use tests\generator\HistoryGenerator;

$faker = Faker\Factory::create();

$generator = new HistoryGenerator([
    'ship_fleet_cid' => Collection::find()->collection(IConstant::COLLECTION_SHIP_TYPES),
    'company_id' => Company::find(),
    'ship_owner_cid' => Collection::find()->collection(IConstant::COLLECTION_SHIP_OWNER_TYPES),
    'ship_status_cid' => Collection::find()->collection(IConstant::COLLECTION_SHIP_STATUSES),
    'title' => function () use ($faker) {
        return $faker->name();
    },
    'short_title' => function () use ($faker) {
        return $faker->name();
    },
    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer sed libero purus. Sed ac metus est, non rutrum elit. Fusce ac nibh vel lectus cursus lobortis at eget dolor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed euismod tincidunt nisi quis consectetur. Nunc venenatis mi vitae nibh placerat nec congue odio semper. Nulla felis elit, laoreet sit amet vehicula sit amet, varius eget elit. Fusce sed mi eu turpis auctor tincidunt a sed augue. Phasellus in nibh nisi, ut ultricies eros. Quisque eget libero in metus suscipit pulvinar. Mauris placerat pretium nunc vitae consequat. Phasellus ac arcu nec lorem hendrerit semper. Aenean sit amet nunc ac mi ornare vestibulum. Nunc sit amet vehicula leo.',
    'food_price' => function () {
        return random_int(300, 600);
    },
    'child_food_price' => function () {
        return random_int(300, 600);
    },
    'view_cabin_in_search' => true,
    'image_fid' => null,
    'image_svg' => '',
]);

return $generator->generate(5);
