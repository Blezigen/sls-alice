<?php

namespace common\modules\acquiring\events;

use yii\base\Event;

class CanDepositPaymentEvent extends Event
{
    public string $orderId;
    public string $acquiringOrderId;

    protected int|float|null $depositAmount = null;

    public function acceptDeposit($depositAmount)
    {
        $this->depositAmount = $depositAmount;
    }

    public function canDeposit() : bool
    {
        return $this->depositAmount !== null;
    }

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