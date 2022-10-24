<?php

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use common\models\City;
use common\models\Ship;

$cities = City::find()->all();

$data = [];
/** @var Ship[] $ships */
$ships = Ship::find()->all();

$id = 1;

foreach ($ships as $ship) {
    $period = CarbonPeriod::create(Carbon::now()->startOfYear(),
        Carbon::now()->endOfYear());

    $idx = 0;
    foreach ($period as $date) {
        $dateTime = $date->format('Y-m-d');

        if (!isset($cities[$idx])) {
            $idx = 0;
        }

        $data[] = [
            'city_id' => $cities[$idx]->id,
            'ship_id' => $ship->id,
            'departure_dt' => \Carbon\Carbon::parse($dateTime)->startOfDay(),
            'arrival_dt' => \Carbon\Carbon::parse($dateTime)->endOfDay(),
        ];
        ++$idx;
        ++$id;
    }
}

return $data;
