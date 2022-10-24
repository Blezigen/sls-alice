<?php

//"start_dt" => \Carbon\Carbon::parse("2022-01-01")->startOfDay(),
//"end_dt" => \Carbon\Carbon::parse("9999-12-01")->endOfDay(),

use Carbon\Carbon;
use common\IConstant;
use common\models\Collection;
use tests\generator\HistoryGenerator;

$faker = new \Faker\Generator();

$generator = new HistoryGenerator([
    "discount_type_cid" => Collection::find()->collection(IConstant::COLLECTION_DISCOUNT_TYPE),
    "discount_cause_type_cid" => Collection::find()->collection(IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES),
    "start_dt" => Carbon::now(),
    "end_dt" => Carbon::now(),
    "value" => function() use($faker) { return $faker->numberBetween(1, 100); },
]);

return array_merge([
    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_BASE
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_1
        ],
        "start_dt" => null,
        "end_dt" => null,
        "value" => 5,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],

    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_BASE
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_2
        ],
        "start_dt" => null,
        "end_dt" => null,
        "value" => 6,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],

    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_BASE
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_3
        ],
        "start_dt" => null,
        "end_dt" => null,
        "value" => 7,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],

    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_BASE
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_4
        ],
        "start_dt" => null,
        "end_dt" => null,
        "value" => 8,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],

    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_BASE
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_5
        ],
        "summary" => json_encode([
            "days" => 30
        ]),

        "start_dt" => null,
        "end_dt" => null,
        "value" => 8,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],

    [
        "discount_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_TYPE => IConstant::DISCOUNT_TYPE_CLIENT
        ],
        "discount_cause_type_cid" => [
            IConstant::COLLECTION_DISCOUNT_ADVANCE_CAUSES => IConstant::DISCOUNT_ADVANCE_CAUSE_TYPE_4
        ],
        "start_dt" => null,
        "end_dt" => null,
        "value" => 8,

        "created_at" => Carbon::now(),
        "updated_at" => null,
        "deleted_at" => null,
        "created_acc" => null,
        "updated_acc" => null,
        "deleted_acc" => null,
    ],
], $generator->generate());