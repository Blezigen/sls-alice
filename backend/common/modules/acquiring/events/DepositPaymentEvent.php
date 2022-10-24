<?php

namespace common\modules\acquiring\events;

use yii\base\Event;

class DepositPaymentEvent extends Event
{
    public string $orderId;
    public string $acquiringOrderId;
    public int|float|null $depositAmount = null;

    public function getOrderId() : string
    {
        return $this->orderId;
    }

    public function getAcquiringOrderId() : string
    {
        return $this->acquiringOrderId;
    }

    public function getDepositAmount() : int|float|null
    {
        return $this->depositAmount;
    }
}