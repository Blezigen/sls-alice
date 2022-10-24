<?php

$idx = 1;
$numbers = [
    '100',
    '101',
    '102',
    '103',
    '104',
    '105',
    '106',
    '107',
    '108',
    '109',
    '110',
    '111',
    '112',
    '113',
    '114',
    '115',
    '116',
    '117',
    '118',
    '119',
];

$result = [];
/** @var \common\models\Ship[] $ships */
$ships = \common\models\Ship::find()->with('shipCabinClasses')->all();
foreach ($ships as $ship) {
    $numbered = array_merge($numbers, []);
    foreach ($ship->shipCabinClasses as $cabinClass) {
        for ($i = 0; $i < random_int(1, 3); ++$i) {
            shuffle($numbered);
            $result[] = [
                'id' => $idx++,
                'ship_cabin_class_id' => $cabinClass->id,
                'ship_id' => $ship->id,
                'number' => array_pop($numbered),
            ];
        }
    }
}

return $result;
