<?php

namespace common\modules\acquiring\contracts;


use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\PaymentAcquiringData;

interface IAcquiringProvider
{
    public const EVENT_CAN_DEPOSIT = "acquiring_can_deposit";
    public const EVENT_DEPOSITED = "acquiring_deposited";
    public const EVENT_DENIED = "acquiring_denied";
    public const EVENT_CANCEL = "acquiring_cancel";

    public function generatePaymentLink(AcquiringData $data) : PaymentAcquiringData;

    public function info($acquiringOrderId, $orderId);

    public function deposit($acquiringOrderId, $amount);

    public function processCallbackRequest(\yii\base\Request $request);
}
