<?php

namespace common\modules\acquiring\events;

use yii\base\Event;

class CancelPaymentEvent extends Event
{
    public string $orderId;
    public string $acquiringOrderId;

    public function getOrderId() : string
    {
        return $this->orderId;
    }

    public function getAcquiringOrderId() : string
    {
        return $this->acquiringOrderId;
    }
}