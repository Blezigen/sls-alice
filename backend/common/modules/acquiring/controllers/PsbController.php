<?php

namespace common\modules\acquiring\controllers;

use common\AbstractController;
use common\components\services\PSBService;
use common\IConstant;
use common\models\OrderPayment;
use common\modules\acquiring\providers\PsbAcquiringProvider;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\Response;

class PsbController extends AbstractController
{
    public $enableCsrfValidation = false;

    public function actionPay($id)
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $provider = 'psb';
        /** @var PsbAcquiringProvider $providerObject */
        $providerObject = \Yii::$container->get($provider);
        $orderPayment = OrderPayment::find()
            ->where(['id' => $id])
            ->one();

        $data = $providerObject->getPaymentPageData(
            amount: $orderPayment->payment_amount,
            orderId: $orderPayment->order_id,
            email: $orderPayment->order->contractor->email,
            description: $orderPayment->description,
            backRefUrl: Url::toRoute(['/acquiring/acquiring/back', 'provider' => $provider, 'id' => $id], true),
            notifyUrl: Url::toRoute(['/acquiring/acquiring/notify', 'provider' => $provider], true),
        );

//
//        $orderPayments = OrderPayment::find()
//            ->where(['id' => $orderPaymentsId])
//            ->one();
//
//        if (!$orderPayments) {
//            throw new Exception('Ссылка не найдена');
//        }
//
//        $PSBService = new PSBService();
//        $pay = $PSBService->getPayments($orderPayments);

        return $this->render('index', $data);
    }

    public function actionBackref($id)
    {
        /** @var OrderPayment $orderPayment */
        $orderPayment = OrderPayment::find()
            ->where(['id' => $id])
            ->one();

        if (!$orderPayment) {
            throw new Exception('Ссылка не найдена');
        }

        if ($orderPayment->statusSlug == IConstant::ORDER_PAYMENT_STATUS_DEPOSIT) {
            return $this->redirect($orderPayment->success_url);
        }

        return $this->redirect($orderPayment->fallback_url);
    }

    public function actionPayback()
    {
        if ($this->request->post()) {
            $PSBService = new PSBService();
            $PSBService->eventPost($this->request);
        }
    }
}
