<?php


namespace common\modules\acquiring;

use yii\base\Model;

class PaymentAcquiringData extends Model
{
    /** @var string Номер заказ в Acquiring */
    public string $acquiringOrderId;

    /** @var string Ссылка для оплаты */
    public string $paymentUrl;

}