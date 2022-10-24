<?php

namespace common\components\services;

class MailService
{
    // public $baseUrl = "https://smsc.ru/sys";
    public $login = null;

    public $username = null;
    public $password = null;
    public $host = null;
    public $port = '465';
    public $encryption = 'ssl';
    public $from = null;

    public function __construct()
    {
        $this->from = \Yii::$app->params['smtpFrom'];
        $this->host = \Yii::$app->params['smtpHost'];
        $this->post = \Yii::$app->params['smtpPort'];
        $this->username = \Yii::$app->params['smtpUsername'];
        $this->password = \Yii::$app->params['smtpPassword'];
    }

    public static function send($email, $subject, $message)
    {
        $service = new self();

        $mailer = \Yii::$app->mailer;
        $mailer->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => $service->host,
            'username' => $service->username,
            'password' => $service->password,
            'port' => $service->port,
            'encryption' => $service->encryption,
        ]);

        $from = $service->from ? $service->from : $service->username;

        $response = \Yii::$app->mailer->compose('@common/mail/simple',
            ['message' => $message])
            ->setFrom([$from])
            ->setTo($email)
            ->setSubject($subject)
            ->send();

        return $response;
    }
}
