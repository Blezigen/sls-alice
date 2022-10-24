<?php

namespace common\notifications;

use common\models\Waiting;
use tuyakhov\notifications\messages\DatabaseMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class ListWaitingNotification implements NotificationInterface
{
    use NotificationTrait;

    public function __construct(
        private Waiting $waiting
    ) {}

    public function exportForDatabase(): DatabaseMessage
    {
        return new DatabaseMessage([
            'subject' => "Каюта освободилась",
            'body' => "Каюта освободилась",
            'data' => [
                'waiting_id' => $this->waiting->id
            ]
        ]);
    }
}