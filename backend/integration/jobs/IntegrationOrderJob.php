<?php

namespace integration\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;
use integration\services\RussiatourismService;

class IntegrationOrderJob extends BaseObject implements JobInterface
{
    public int $id;

    public function execute($queue)
    {
        $service = new RussiatourismService();
        $result = $service->CreateVoucherByOrderID($this->id);

        return $result;
    }
}
