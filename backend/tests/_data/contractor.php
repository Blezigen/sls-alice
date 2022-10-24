<?php

$faker = Faker\Factory::create("RU_ru");

$generatorLegal = new \tests\generator\HistoryGenerator([
    'status_cid'           => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_STATUSES),
    'contractor_type_cid'  => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_TYPES)
        ->andWhere([
            "NOT IN",
            "slug",
            [
                \common\IConstant::CONTRACTOR_LEGAL_ENTITY,
            ]
        ]),
    //    'voucher_stamp_fid' => null,
    //    'contract_fid' => null,
    'region_id'            => \common\models\Region::find(),
    'city_id'              => \common\models\City::find(),
    'account_id'           => null,
    'bank_detail_id'       => null,
    'fin_support_id'       => null,
    'agent_id'             => null,
    'company_id'           => \common\models\Company::find(),
    'affiliate_id'         => null,
    'org_position_cid'     => null,
    'identity_document_id' => null,

    'contractor_fn'  => function () use ($faker){
        return $faker->company;
    },
    'email'          => null,
    'phone_1'        => null,
    'phone_2'        => null,
    'phone_3'        => null,
    'fax'            => null,
    'legal_address'  => null,
    'fact_address'   => null,
    'fact_post_code' => null,

    'commission_self'   => 0,
    'commission_other'  => 0,
    'director_fn'       => null,
    'director_position' => null,
    'book_fn'           => null,

    'is_can_subagent'       => null,
    'is_can_print_vouchers' => null,
    'is_operator'           => null,

    'operator_number' => null,
    'web'             => null,

    'is_member' => null,

    'doc_number_1' => null,
    'doc_number_2' => null,
    'doc_number_3' => null,

    'discount_number' => null,
    'discount_amount' => null,
    'work_place'      => null,

    'letter_of_attorney_fid' => null,
    'signature_fid'          => null,

    'created_at'  => \Carbon\Carbon::now(),
    'created_acc' => null,
    'updated_at'  => null,
    'updated_acc' => null,
    'deleted_at'  => null,
    'deleted_acc' => null,
]);

$generatorEmployee = new \tests\generator\HistoryGenerator([
    'status_cid'           => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_STATUSES),
    'contractor_type_cid'  => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_TYPES)
        ->andWhere([
            'NOT IN',
            "slug",
            [
                \common\IConstant::CONTRACTOR_EMPLOYEE,
            ]
        ]),
    'region_id'            => \common\models\Region::find(),
    'city_id'              => \common\models\City::find(),
    'account_id'           => null,
    'bank_detail_id'       => null,
    'fin_support_id'       => null,
    'agent_id'             => null,
    'company_id'           => \common\models\Company::find(),
    'affiliate_id'         => null,
    'org_position_cid'     => null,
    'identity_document_id' => null,

    'contractor_fn'  => function () use ($faker){
        return 'Сотрудник "'.$faker->name.'"';
    },
    'email'          => null,
    'phone_1'        => null,
    'phone_2'        => null,
    'phone_3'        => null,
    'fax'            => null,
    'legal_address'  => null,
    'fact_address'   => null,
    'fact_post_code' => null,

    'commission_self'   => 0,
    'commission_other'  => 0,
    'director_fn'       => null,
    'director_position' => null,
    'book_fn'           => null,

    'is_can_subagent'       => null,
    'is_can_print_vouchers' => null,
    'is_operator'           => null,

    'operator_number' => null,
    'web'             => null,

    'is_member' => null,

    'doc_number_1' => null,
    'doc_number_2' => null,
    'doc_number_3' => null,

    'discount_number' => null,
    'discount_amount' => null,
    'work_place'      => null,

    'letter_of_attorney_fid' => null,
    'signature_fid'          => null,

    'created_at'  => \Carbon\Carbon::now(),
    'created_acc' => null,
    'updated_at'  => null,
    'updated_acc' => null,
    'deleted_at'  => null,
    'deleted_acc' => null,
]);

$generatorPhysical = new \tests\generator\HistoryGenerator([
    'status_cid'           => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_STATUSES),
    'contractor_type_cid'  => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_TYPES)
        ->andWhere([
            "IN",
            "slug",
            [
                \common\IConstant::CONTRACTOR_PHYSICAL_PERSON
            ]
        ]),
    //    'voucher_stamp_fid' => null,
    //    'contract_fid' => null,
    'region_id'            => \common\models\Region::find(),
    'city_id'              => \common\models\City::find(),
    'account_id'           => null,
    'bank_detail_id'       => null,
    'fin_support_id'       => null,
    'agent_id'             => null,
    'company_id'           => \common\models\Company::find(),
    'affiliate_id'         => null,
    'org_position_cid'     => null,
    'identity_document_id' => null,

    'contractor_fn'  => function () use ($faker){
        return "{$faker->lastName} {$faker->firstName} {$faker->lastName}";
    },
    'email'          => null,
    'phone_1'        => null,
    'phone_2'        => null,
    'phone_3'        => null,
    'fax'            => null,
    'legal_address'  => null,
    'fact_address'   => null,
    'fact_post_code' => null,

    'commission_self'   => function () {
        return random_int(1, 5);
    },
    'commission_other'  => function () {
        return random_int(5, 10);
    },
    'director_fn'       => null,
    'director_position' => null,
    'book_fn'           => null,

    'is_can_subagent'       => null,
    'is_can_print_vouchers' => null,
    'is_operator'           => null,

    'operator_number' => null,
    'web'             => null,

    'is_member' => null,

    'doc_number_1' => null,
    'doc_number_2' => null,
    'doc_number_3' => null,

    'discount_number' => null,
    'discount_amount' => null,
    'work_place'      => null,

    'letter_of_attorney_fid' => null,
    'signature_fid'          => null,

    'created_at'  => \Carbon\Carbon::now(),
    'created_acc' => null,
    'updated_at'  => null,
    'updated_acc' => null,
    'deleted_at'  => null,
    'deleted_acc' => null,
]);

$generatorAgent = new \tests\generator\HistoryGenerator([
    'status_cid'           => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_STATUSES),
    'contractor_type_cid'  => \common\models\Collection::find()
        ->collection(\common\IConstant::COLLECTION_CONTRACTOR_TYPES)
        ->andWhere([
            "IN",
            "slug",
            [
                \common\IConstant::CONTRACTOR_AGENT
            ]
        ]),
    //    'voucher_stamp_fid' => null,
    //    'contract_fid' => null,
    'region_id'            => \common\models\Region::find(),
    'city_id'              => \common\models\City::find(),
    'account_id'           => null,
    'bank_detail_id'       => null,
    'fin_support_id'       => null,
    'agent_id'             => null,
    'company_id'           => \common\models\Company::find(),
    'affiliate_id'         => null,
    'org_position_cid'     => null,
    'identity_document_id' => null,

    'contractor_fn'  => function () use ($faker){
        return 'Агент: '.$faker->company;
    },
    'email'          => null,
    'phone_1'        => null,
    'phone_2'        => null,
    'phone_3'        => null,
    'fax'            => null,
    'legal_address'  => null,
    'fact_address'   => null,
    'fact_post_code' => null,

    'commission_self'   => function () {
        return random_int(1, 5);
    },
    'commission_other'  => function () {
        return random_int(5, 10);
    },
    'director_fn'       => null,
    'director_position' => null,
    'book_fn'           => null,

    'is_can_subagent'       => null,
    'is_can_print_vouchers' => null,
    'is_operator'           => null,

    'operator_number' => null,
    'web'             => null,

    'is_member' => null,

    'doc_number_1' => null,
    'doc_number_2' => null,
    'doc_number_3' => null,

    'discount_number' => null,
    'discount_amount' => null,
    'work_place'      => null,

    'letter_of_attorney_fid' => null,
    'signature_fid'          => null,

    'created_at'  => \Carbon\Carbon::now(),
    'created_acc' => null,
    'updated_at'  => null,
    'updated_acc' => null,
    'deleted_at'  => null,
    'deleted_acc' => null,
]);

return array_merge(
    $generatorLegal->generate(10),
    $generatorEmployee->generate(10),
    $generatorPhysical->generate(50),
    $generatorAgent->generate(25)
);
