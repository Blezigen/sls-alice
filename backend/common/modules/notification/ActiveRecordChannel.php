<?php

namespace common\modules\notification;

use tuyakhov\notifications\messages\DatabaseMessage;
use tuyakhov\notifications\NotifiableInterface;
use tuyakhov\notifications\NotificationInterface;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\helpers\Json;

class ActiveRecordChannel extends \tuyakhov\notifications\channels\ActiveRecordChannel
{
    public $model = Notification::class;

    public function send(NotifiableInterface $recipient, NotificationInterface $notification)
    {
        $model = \Yii::createObject($this->model);

        if (!$model instanceof BaseActiveRecord) {
            throw new InvalidConfigException('Model class must extend from \\yii\\db\\BaseActiveRecord');
        }

        /** @var DatabaseMessage $message */
        $message = $notification->exportFor('database');
        list($notifiableType, $notifiableId) = $recipient->routeNotificationFor('database');
        $data = [
            'level' => $message->level,
            'subject' => $message->subject,
            'body' => $message->body,
            "notifiable_slug" => $message->data["notifiable_slug"] ?? null,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'data' => Json::encode($message->data),
        ];

        if ($model->load($data, '')) {
            return $model->insert();
        }

        return false;
    }
}