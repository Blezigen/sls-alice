<?php

namespace tests\fixtures;

use common\models\OrderPlace;
use Symfony\Component\Console\Helper\ProgressBar;
use tests\modules\HistoryActiveFixture;

class OrderPlacesFixture extends HistoryActiveFixture
{
    public $modelClass = OrderPlace::class;
    public $dataFile = __DIR__ . '/../_data/order_places.php';

    public $depends
        = [
            OrderCabinFixture::class,
            IdentityDocumentFixture::class,
            DiscountCardFixture::class,
            CollectionFixture::class,
            DiscountFixture::class,
        ];

    public function getDeleteRuleName()
    {
        return 'chk_delete_order_places';
    }

    public function load()
    {
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('file');
        $progressBar->setMessage(static::class);
//        echo str_pad(static::class, 50, "_");
        try {
            $this->data = [];
            $table = $this->getTableSchema();
            $data = $this->getData();
            $dataCount = count($data);
            $progressBar->setMaxSteps($dataCount);

            foreach ($data as $alias => $row) {
                if (method_exists($this, 'afterInsert')) {
                    $this->afterInsert($row);
                }
                $command = $this->db->createCommand('CALL order_places_insert(
                    :p_id,
                    :p_order_cabin_id,
                    :p_cabin_place_id,
                    :p_insurance_cid,
                    :p_identity_document_id,
                    :p_travel_package_cid,
                    :p_discount_card_id,
                    :p_gender_cid,
                    :p_discount_category_default_id,
                    :p_discount_category_early_id,
                    :p_discount_category_constant_id,
                    :p_discount_category_online_id,
                    :p_total_changed_price,
                    :p_place_type_cid,
                    :account
                )');
                $row['id'] = null;
                $row['account'] = null;
                $command->bindParam('p_id', $row['id']);
                $command->bindParam('p_order_cabin_id', $row['order_cabin_id']);
                $command->bindParam('p_cabin_place_id', $row['cabin_place_id']);
                $command->bindParam('p_insurance_cid', $row['insurance_cid']);
                $command->bindParam('p_identity_document_id', $row['identity_document_id']);
                $command->bindParam('p_travel_package_cid', $row['travel_package_cid']);
                $command->bindParam('p_discount_card_id', $row['discount_card_id']);
                $command->bindParam('p_gender_cid', $row['gender_cid']);
                $command->bindParam('p_discount_category_default_id', $row['discount_category_default_id']);
                $command->bindParam('p_discount_category_early_id', $row['discount_category_early_id']);
                $command->bindParam('p_discount_category_constant_id', $row['discount_category_constant_id']);
                $command->bindParam('p_discount_category_online_id', $row['discount_category_online_id']);
                $command->bindParam('p_total_changed_price', $row['total_changed_price']);
                $command->bindParam('p_place_type_cid', $row['place_type_cid']);
                $command->bindParam('account', $row['account']);
                $command->execute();
                $progressBar->advance();
            }
            unset($data);
        } catch (\Throwable $e) {
            dd(static::class, $e->getMessage());
        }
        $progressBar->finish();

//        echo " dataCount: ".count($this->data).PHP_EOL;
    }
}
