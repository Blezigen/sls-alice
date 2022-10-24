<?php

namespace tests\fixtures;

use Carbon\Carbon;
use common\models\Order;
use Symfony\Component\Console\Helper\ProgressBar;
use tests\modules\HistoryActiveFixture;

class OrderFixture extends HistoryActiveFixture
{
    public $count = [];
    public $modelClass = Order::class;
    public $dataFile = __DIR__ . '/../_data/orders.php';

    public $depends
        = [
            TourCabinPriceFixture::class,
            TourFixture::class,
            ContractorFixture::class,
            ShipFixture::class,
            CabinFixture::class,
        ];

    public function startAtId()
    {
        return Carbon::now()->format('md00000');
    }

    public function beforeLoad()
    {
        $start = intval($this->startAtId());
        $query = \Yii::$app->db->createCommand("ALTER SEQUENCE orders_id_seq RESTART WITH $start");

        $query->execute();
        parent::beforeLoad();
    }

    public function afterLoad()
    {
        $start = Order::find()->select('id')->orderBy('id DESC')->one();
        $start = $start->id + 1;
        $query = \Yii::$app->db->createCommand("ALTER SEQUENCE orders_id_seq RESTART WITH $start");
        $query->execute();
        parent::beforeLoad();
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
                $command = $this->db->createCommand('CALL orders_insert(
                    :p_id,
                    :p_order_type_cid,
                    :p_manager_uid,
                    :p_tour_id,
                    :p_contractor_id,
                    :p_reserve_end_date,
                    :p_commission_delta,
                    :p_comment,
                    :p_send_comment_to_director,
                    :p_ignore_payment,
                    :p_ignore_report,
                    :p_payment_status_cid,
                    :account
                )');
                $row['id'] = null;
                $row['account'] = null;
                $command->bindParam('p_id', $row['id']);
                $command->bindParam('p_order_type_cid', $row['order_type_cid']);
                $command->bindParam('p_manager_uid', $row['manager_uid']);
                $command->bindParam('p_tour_id', $row['tour_id']);
                $command->bindParam('p_contractor_id', $row['contractor_id']);
                $command->bindParam('p_reserve_end_date', $row['reserve_end_date']);
                $command->bindParam('p_commission_delta', $row['commission_delta']);
                $command->bindParam('p_comment', $row['comment']);
                $command->bindParam('p_send_comment_to_director', $row['send_comment_to_director']);
                $command->bindParam('p_ignore_payment', $row['ignore_payment']);
                $command->bindParam('p_ignore_report', $row['ignore_report']);
                $command->bindParam('p_payment_status_cid', $row['payment_status_cid']);
                $command->bindParam('account', $row['account']);
                $command->execute();
//                $this->data[$alias] = array_merge($row, $primaryKeys);
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
