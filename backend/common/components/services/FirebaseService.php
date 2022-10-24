<?php

namespace common\components\services;

use yii\httpclient\Client;

class FirebaseService
{
    public $baseUrl = 'https://fcm.googleapis.com/fcm';
    public $serverKey = null;
    public $senderId = null;

    public function __construct()
    {
        $this->senderId = \Yii::$app->params['firebaseSenderId'];
        $this->serverKey = \Yii::$app->params['firebaseServerKey'];
    }

    public static function send($clientTokenId, $subject, $message)
    {
        $service = new self();

        $client = new Client([
            'baseUrl' => $service->baseUrl,
        ]);

        $params = [
            'to' => $clientTokenId,
            'notification' => [
                'title' => $subject,
                'body' => $message,
                'icon' => \Yii::$app->params['frontendUrl']
                    . '/img/firebase_logo.png', // 192x192
                'click_action' => \Yii::$app->params['frontendUrl'],
            ],
        ];

        $headers = [
            'Content-type' => 'application/json',
            'Authorization' => 'key=' . $service->serverKey,
        ];

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('send')
            ->addHeaders($headers)
            ->setData($params)
            ->send();

        return !empty($response->data) ? true : false;
    }
}
