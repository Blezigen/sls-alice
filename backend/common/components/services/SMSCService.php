<?php

namespace common\components\services;

use yii\base\Model;
use yii\httpclient\Client;

class SMSCService extends Model
{
    public $baseUrl = 'https://smsc.ru/sys';
    public $login = null;
    public $password = null;

    public function __construct()
    {
        $this->login = \Yii::$app->params['smscLogin'];
        $this->password = \Yii::$app->params['smscPassword'];
    }

    public static function send($phone, $message)
    {
        $service = new self();

        $params = [
            'phones' => $phone,
            'mes' => $message,
            'charset' => 'utf-8',
        ];

        return $service->sendRequest($params);
    }

    // {
    //     "id": 4522,
    //     "cnt": 5,
    //     "code": "084204"
    // }

    public static function call($phone)
    {
        $service = new self();

        $params = [
            'phones' => $phone,
            'mes' => 'code',
            'call' => 1,
        ];

        return $service->sendRequest($params);
    }

    private function sendRequest($params)
    {
        $service = new self();

        $params['phones'] = preg_replace('/[^0-9]/', '', $params['phones']);
        $params['phones'] = '7' . substr($params['phones'], -10);

        $params['mes'] = strip_tags($params['mes']);

        $params['login'] = $service->login;
        $params['psw'] = $service->password;
        $params['fmt'] = 3;

        $client = new Client([
            'baseUrl' => $service->baseUrl,
        ]);

        $response = $client->createRequest()
            ->setUrl('send.php')
            ->setData($params)
            ->send();

        return $response->data;
    }
}
