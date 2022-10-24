<?php

namespace common\components\services;

use yii\base\Model;
use yii\httpclient\Client;

class TelegramService extends Model
{
    public $baseUrl = 'https://api.telegram.org/bot';
    public $botToken = null;
    public $parseMode = 'HTML';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public static function send($telegramId, $message, $token = null)
    {
        $service = new self([
            'botToken' => $token,
        ]);

        $client = new Client([
            'baseUrl' => $service->baseUrl . $service->botToken,
        ]);

        $params = [
            'chat_id' => $telegramId,
            'text' => $message,
            'parse_mode' => $service->parseMode,
        ];

        $response = $client->createRequest()
            ->setUrl('sendMessage')
            ->setData($params)
            ->send();

        return $response->data;
    }
}
