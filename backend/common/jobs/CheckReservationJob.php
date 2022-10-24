<?php

namespace common\jobs;

use Carbon\Carbon;
use common\IConstant;
use common\models\Collection;
use common\models\Order;
use common\modules\sender\contracts\ISenderInitiator;
use common\modules\sender\contracts\ISenderReceiver;
use SebastianBergmann\CodeCoverage\Report\PHP;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;

class CheckReservationJob extends BaseObject implements JobInterface
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }


    public function execute($queue)
    {
        $type = Collection::find()
            ->collection(IConstant::COLLECTION_ORDER_TYPE)
            ->slug(IConstant::ORDER_TYPE_TEMP)->one();

        $orderQ = Order::find()->andWhere([
            "and",
            ["order_type_cid" => $type->id],
            ['<', "reserve_end_date", Carbon::now()->format("Y-m-d")]
        ]);

        echo "Query: ".$orderQ->createCommand()->rawSql.PHP_EOL;
        $orders = $orderQ->all();

        echo "Count: ".$orderQ->count().PHP_EOL;

        foreach ($orders as $order){
            echo "Order: {$order->id} - delete".PHP_EOL;
            $order->delete();
        }
        return true;
    }
}
