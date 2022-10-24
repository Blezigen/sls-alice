<?php

namespace common\modules\acquiring\providers;

use Carbon\Carbon;
use common\IConstant;
use common\models\OrderPayment;
use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\events\CancelPaymentEvent;
use common\modules\acquiring\events\CanDepositPaymentEvent;
use common\modules\acquiring\events\DeniedPaymentEvent;
use common\modules\acquiring\events\DepositPaymentEvent;
use common\modules\acquiring\PaymentAcquiringData;
use yii\helpers\Url;

class DefaultAcquiringProvider implements IAcquiringProvider
{
    public const ORDER_REGISTER = 0;
    public const ORDER_PRE_AUTHORIZE = 1;
    public const ORDER_DEPOSITED = "deposited";
    public const ORDER_AUTHORIZE_CANCEL = 'cancel';
    public const ORDER_RETURN = 4;
    public const ORDER_AUTHORIZE = 'authorize';
    public const ORDER_AUTHORIZE_DENIED = 'authorize_denied';



    const PROVIDER = "default";

    public function generatePaymentLink(AcquiringData $data
    ): PaymentAcquiringData {
        $acqOrderNumber = md5($data->orderNumber.date('Y-m-d H:i:s'));

        $paymentUrl = Url::toRoute([
            "/acquiring/default/test",
            "id"               => $data->orderNumber,
            "orderNumber"      => $data->orderNumber,
            "mdOrder"          => $acqOrderNumber,
            "amount"           => $data->amount,
            "callback-notify"  => $data->notifyUrl,
            "callback-success" => $data->successUrl,
            "callback-fail"    => $data->failUrl,
        ]);

        return new PaymentAcquiringData([
            "acquiringOrderId" => $acqOrderNumber,
            "paymentUrl"       => $paymentUrl,
        ]);
    }

    public function info($acquiringOrderId, $orderId)
    {
        $state = \Yii::$app->request->post('state', 'deposited');

        return [
            "orderNumber" => "$orderId",
            "mdOrder"     => "$acquiringOrderId",
            "state"       => $state,
        ];
    }

    public function deposit($acquiringOrderId, $amount)
    {
        return !empty($acquiringOrderId) && !empty($amount);
    }

    public function processCallbackRequest(\yii\base\Request $request)
    {
        $type = $request->post('type');
        $state = $request->post('state');
        $order = $request->post('order');
        $orderId = $request->post('orderNumber');
        $acquiringOrderId = $request->post('mdOrder');

        $info = $this->info($acquiringOrderId, $orderId);

        $orderNumber = $info["orderNumber"] ?? null;
        $mdOrder = $info["mdOrder"] ?? null;

        if ($info["state"] === "pre_authorized"){
            $eventCanDeposit = new CanDepositPaymentEvent([
                "orderId" => $orderNumber,
                "acquiringOrderId" => $mdOrder,
            ]);

            \Yii::$app->trigger(IAcquiringProvider::EVENT_CAN_DEPOSIT, $eventCanDeposit);
            if ($eventCanDeposit->canDeposit()){
                if ($this->deposit($orderId, $eventCanDeposit->getDepositAmount())) {
                    $event = new DepositPaymentEvent([
                        "orderId"          => $orderNumber,
                        "acquiringOrderId" => $mdOrder,
                        "depositAmount" => $eventCanDeposit->getDepositAmount()
                    ]);
                    \Yii::$app->trigger(IAcquiringProvider::EVENT_DEPOSITED, $event);
                }
            }
        }

        if ($info["state"] === "deposited"){
            $eventCanDeposit = new CanDepositPaymentEvent([
                "orderId"          => $orderNumber,
                "acquiringOrderId" => $mdOrder,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_CAN_DEPOSIT, $eventCanDeposit);

//            if ($eventCanDeposit->canDeposit()){
                $event = new DepositPaymentEvent([
                    "orderId"          => $orderNumber,
                    "acquiringOrderId" => $mdOrder,
                    "depositAmount" => $eventCanDeposit->getDepositAmount()
                ]);
                \Yii::$app->trigger(IAcquiringProvider::EVENT_DEPOSITED, $event);
//            }
        }

        if ($info["state"] === "denied"){
            $event = new DeniedPaymentEvent([
                "orderId"          => $orderNumber,
                "acquiringOrderId" => $mdOrder,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_DENIED, $event);
        }

        if ($info["state"] === "cancel"){
            $event = new CancelPaymentEvent([
                "orderId"          => $orderNumber,
                "acquiringOrderId" => $mdOrder
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_CANCEL, $event);
        }
    }
}