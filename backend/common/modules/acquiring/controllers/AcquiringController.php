<?php

namespace common\modules\acquiring\controllers;

use api\exceptions\NotFoundHttpException;
use common\AbstractController;
use common\IConstant;
use common\models\OrderPayment;
use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\permission\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;

class AcquiringController extends AbstractController
{
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge($behaviors, [
            'authenticator' => [
                'optional' => [
                    'pay',
                    'file',
                    'notify',
                    'back',
                    'success',
                    'fail',
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ]);
    }

    public function actionFile($filename)
    {
        $file = \Yii::getAlias("@root/api/web/temp/{$filename}");
        if (file_exists($file)) {
            \Yii::$app->response->format = Response::FORMAT_RAW;
            \Yii::$app->response->headers->add('Content-type', 'image/png');
            \Yii::$app->response->content = file_get_contents($file);
            \Yii::$app->response->send();
        }

        return \Yii::$app->response->redirect(env('FRONTEND_URL'));
    }

    public function actionPay($provider, $id): Response
    {
        /** @var IAcquiringProvider $providerObject */
        $providerObject = \Yii::$container->get($provider);

        /** @var OrderPayment $orderPayment */
        $orderPayment = OrderPayment::find()
            ->andWhere(['payment_type' => $providerObject::PROVIDER])
            ->where(['id' => $id])
            ->one();

        if (!$orderPayment) {
            throw new NotFoundHttpException('Заказ не найден');
        }

        $paymentData = new AcquiringData([
            'orderNumber' => $orderPayment->id,
            'userId' => 1,
            'successUrl' => Url::toRoute(['/acquiring/acquiring/success', 'provider' => $provider, 'id' => $id], true),
            'failUrl' => Url::toRoute(['/acquiring/acquiring/fail', 'provider' => $provider, 'id' => $id], true),
            'backUrl' => Url::toRoute(['/acquiring/acquiring/back', 'provider' => $provider, 'id' => $id], true),
            'notifyUrl' => Url::toRoute(['/acquiring/acquiring/notify', 'provider' => $provider], true),
            'amount' => $orderPayment->payment_amount ?? 0,
            'email' => $orderPayment->order->getEmail(),
            'description' => 'Оплата заказа №' . $orderPayment->order->id,
            'params' => [],
        ]);
        $payment = $providerObject->generatePaymentLink($paymentData);

        $orderPayment->payment_data = $payment;
        $orderPayment->payment_number = $payment->acquiringOrderId;
        $orderPayment->payment_url = $payment->paymentUrl;

        $orderPayment->save();

        return \Yii::$app->response->redirect($payment->paymentUrl);
    }

    public function actionNotify($provider)
    {
        /** @var IAcquiringProvider $providerObject */
        $providerObject = \Yii::$container->get($provider);
        $providerObject->processCallbackRequest($this->request);

        return null;
    }

    public function actionBack($provider, $id)
    {
        /** @var OrderPayment $orderPayment */
        $orderPayment = OrderPayment::findOne($id);

        if ($orderPayment->statusSlug === IConstant::ORDER_PAYMENT_STATUS_DEPOSIT) {
            return \Yii::$app->response->redirect($orderPayment->success_url);
        } else {
            return \Yii::$app->response->redirect($orderPayment->fallback_url);
        }
    }

    public function actionSuccess($provider, $id)
    {
        $orderPayment = OrderPayment::findOne($id);

        return \Yii::$app->response->redirect($orderPayment->success_url);
    }

    public function actionFail($provider, $id)
    {
        $orderPayment = OrderPayment::findOne($id);

        return \Yii::$app->response->redirect($orderPayment->fallback_url);
    }
}
