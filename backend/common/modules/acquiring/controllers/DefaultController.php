<?php

namespace common\modules\acquiring\controllers;

use common\AbstractController;
use common\modules\permission\AccessControl;
use GuzzleHttp\Client;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class DefaultController extends AbstractController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return ArrayHelper::merge($behaviors, [
            'authenticator' => [
                'optional' => [
                    'test',
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

    public function actionTest()
    {
        if ($this->request->isGet) {
            $state = $this->request->get('state', 'index');
            $orderNumber = $this->request->get('orderNumber');
            $mdOrder = $this->request->get('mdOrder');
            $amount = $this->request->get('amount');
            $redirectNotify = $this->request->get('callback-notify', null);
            $redirectSuccess = $this->request->get('callback-success', null);
            $redirectFail = $this->request->get('callback-fail', null);
            \Yii::$app->response->format = Response::FORMAT_HTML;

            return $this->render($state, [
                'orderNumber' => $orderNumber,
                'mdOrder' => $mdOrder,
                'amount' => $amount,
                'callbackSuccess' => $redirectSuccess,
                'callbackFail' => $redirectFail,
                'callbackNotify' => $redirectNotify,
            ]);
        }

        $state = $this->request->post('state', 'success');
        $orderNumber = $this->request->post('orderNumber');
        $mdOrder = $this->request->post('mdOrder');
        $message = $this->request->post('message');
        $amount = $this->request->post('amount');
        $redirectNotify = $this->request->post('notify', null);
        $redirectSuccess = $this->request->post('success', null);
        $redirectFail = $this->request->post('fail', null);

        try {
            $client = new Client();
            $response = $client->post($redirectNotify, [
                'json' => [
                    'orderNumber' => $orderNumber,
                    'mdOrder' => $mdOrder,
                    'amount' => $amount,
                    'state' => $state,
                ],
            ]);
        } catch (\Throwable $ex) {
            \Yii::error($ex->getMessage());
        }

        \Yii::$app->response->format = Response::FORMAT_HTML;

        return $this->render('result', [
            'message' => $message,
            'state' => $state,
            'orderNumber' => $orderNumber,
            'mdOrder' => $mdOrder,
            'amount' => $amount,
            'callbackSuccess' => $redirectSuccess,
            'callbackFail' => $redirectFail,
            'callbackNotify' => $redirectNotify,
        ]);
    }
}
