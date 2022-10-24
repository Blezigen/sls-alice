<?php

namespace common\components\services;

use common\exceptions\ValidationException;
use common\IConstant;
use common\models\Collection;
use common\models\Order;
use common\models\OrderPayment;
use common\modules\acquiring\AcquiringData;
use yii\base\Exception;

class OrderPaymentsService
{
    public const PSB_PRODIDER = 'PSBProvider';

    public function createPayments(AcquiringData $data)
    {
        if ($data->params['provider'] == self::PSB_PRODIDER) {
            $order = Order::find()
                ->withoutTrashed()
                ->byId($data->orderNumber)
                ->one();

            if (!$order) {
                throw new Exception("Заказ № {$data->orderNumber} не найден в системе");
            }

            $status = Collection::find()
                ->where(['slug' => IConstant::PAYMENT_STATUS_NOT_PAYED])
                ->one();

            $order_payments = new OrderPayment();
            $order_payments->order_id = $data->orderNumber;
            $order_payments->payment_amount = $data->amount;
            $order_payments->status_cid = $status->id;
            $order_payments->description = "Отплата заказа № {$data->orderNumber}";
            $order_payments->success_url = $data->successUrl;
            $order_payments->fallback_url = $data->failUrl;

            if (!$order_payments->validate()) {
                throw new ValidationException($order_payments->errors);
            }

            if ($order_payments->save()) {
                $redirect_url = env('API_URL') . '/acquiring/psb/' . $order_payments->id;
                $order_payments->redirect_url = $redirect_url;
                $order_payments->save();

                return $order_payments->redirect_url;
            }
        }

        throw new Exception('Неверно указан провайдер');
    }
}
