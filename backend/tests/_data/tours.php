<?php

use Carbon\Carbon;
use common\models\Collection;
use common\models\Company;
use common\models\Ship;
use tests\generator\AssociationHelper;
use tests\generator\HistoryGenerator;

$faker = Faker\Factory::create();
//
//$generator = new HistoryGenerator([
//    'parent_tour_id' => null,
//    'first_service_cid' => Collection::find()->collection('ship-first-last-services'),
//    'last_service_cid' => Collection::find()->collection('ship-first-last-services'),
//    'food_type_cid' => Collection::find()->collection('feedings'),
//    'company_id' => Company::find(),
//    function() use ($faker){
//        $shipId = AssociationHelper::create(Ship::find())->generate();
//        $ship = Ship::findOne($shipId);
//        $shipNavigation = $ship->getShipNavigations();
//
//        $date1 = Carbon::now()->setDay($faker->randomNumber(1,28))->setMonth($faker->randomNumber(1,12));
//        $date2 = Carbon::now()->setDay($faker->randomNumber(1,28))->setMonth($faker->randomNumber(1,12));
//
//
//        return [
//            'ship_id' => $shipId,
//            'departure_dt' => min($date1, $date2),
//            'arrival_dt' => max($date1,$date2),
//        ];
//    },
//    'plan_income' => function() use($faker) { return $faker->numberBetween(1000, 10000); },
//    'plan_income_k' => function() use($faker) { return $faker->numberBetween(0, 100); },
//    'commission_disable' => function() { return mt_rand(0, 1) > 0.5; },
//    'commission_amount' => function() use($faker) { return $faker->numberBetween(200, 1000); },
//    'comment' => function() use($faker) { return $faker->text(); },
//    'public_comment' => function() use($faker) { return $faker->text(); },
//    'traffic_schedule_fid' => null,
//    'extra_program_fid' => null,
//    'extra_program_title' => null,
//    'description_spo' => null,
//    'visible_prices' => function() { return mt_rand(0, 1) > 0.5; },
//    'is_visible' => function() { return mt_rand(0, 1) > 0.5; },
//    'is_canceled' => function() { return mt_rand(0, 1) > 0.5; },
//    'coefficient_name' => 'K',
//]);
//
//$tours = $generator->generate(100);

$query = (new \yii\db\Query())
    ->select('ships.id as ship_id')
    ->from('ships');

$ships = $query->all();

$faker = new \Faker\Generator();

foreach ($ships as $ship){
    for($i = 0 ; $i <= $faker->numberBetween(100,1000); $i++) {
        $date1 = Carbon::now()->setDay($faker->numberBetween(1, 27))
            ->setMonth($faker->numberBetween(1, 12));
        $date2 = Carbon::now()->setDay($faker->numberBetween(1, 27))
            ->setMonth($faker->numberBetween(1, 12));


        $departureDt = Carbon::parse(max($date1, $date2))->startOfDay()
            ->addHours(3);
        $arrivalDt = Carbon::parse(min($date1, $date2))->endOfDay()
            ->subHours(3);

        $tours[] = [
            'parent_tour_id'       => null,
            'first_service_cid'    => [
                \common\IConstant::COLLECTION_SHIP_FIRST_LAST_SERVICES => \common\IConstant::SFLS_NOT
            ],
            'last_service_cid'     => [
                \common\IConstant::COLLECTION_SHIP_FIRST_LAST_SERVICES => \common\IConstant::SFLS_NOT
            ],
            'food_type_cid'        => [
                \common\IConstant::COLLECTION_FEEDING => \common\IConstant::FEEDING_UNDEFINED
            ],
            'company_id'           => null,
            'ship_id'              => $ship["ship_id"],
            'departure_dt'         => $departureDt,
            'arrival_dt'           => $arrivalDt,
            'plan_income'          => '10000',
            'plan_income_k'        => '10',
            'commission_disable'   => false,
            'commission_amount'    => 10,
            'comment'              => 'Комментарий к туру',
            'public_comment'       => 'Тестовый тур',
            'traffic_schedule_fid' => null,
            'extra_program_fid'    => null,
            'extra_program_title'  => null,
            'description_spo'      => null,
            'visible_prices'       => true,
            'visible_dt'           => Carbon::now(),
            'is_canceled'          => false,
            'coefficient_name'     => 'K',
            'created_at'           => Carbon::now(),
            'updated_at'           => null,
            'deleted_at'           => null,
            'created_acc'          => null,
            'updated_acc'          => null,
            'deleted_acc'          => null,
        ];
    }
}

return $tours;