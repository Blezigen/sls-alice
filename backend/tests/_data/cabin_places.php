<?php

$idx = 0;
$numbers = [
    'a',
    'б',
    'в',
    'г',
    'д',
    'е',
    'ё',
    'ж',
    'з',
    'и',
    'й',
];

function cabinPlace(int &$idx, $cabinId, $number)
{
    $types = [
        \common\IConstant::CABIN_PLACE_TYPE_TOP,
        \common\IConstant::CABIN_PLACE_TYPE_BOTTOM,
    ];

    return [
        'id' => $idx++,

        'ship_cabin_class_id' => $cabinId,
        'svg_id' => 1,
        'place_type_cid' => [
            \common\IConstant::COLLECTION_CABIN_PLACE_TYPES => $types[random_int(0, count($types) - 1)],
        ],
        'number' => $number,

        'created_at' => \Carbon\Carbon::now(),
        'created_acc' => null,
    ];
}
$result = [];
/** @var \common\models\Ship[] $ships */
$ships = \common\models\Ship::find()->all();
foreach ($ships as $ship) {
    $numbered = array_merge($numbers, []);
    foreach ($ship->shipCabinClasses as $class) {
        shuffle($numbered);
        for ($i = 0; $i < $class->max_place_base; ++$i) {
            $result[] = cabinPlace($idx, $class->id, array_pop($numbered));
        }

        for ($i = 0; $i < $class->max_place_advance; ++$i) {
            $result[] = cabinPlace($idx, $class->id, array_pop($numbered));
        }
    }
}

return $result;
