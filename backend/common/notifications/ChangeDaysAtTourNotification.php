<?php

namespace common\notifications;

use common\models\Tour;
use tuyakhov\notifications\messages\DatabaseMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class ChangeDaysAtTourNotification implements NotificationInterface
{
    use NotificationTrait;

    public function __construct(
        private Tour $tour
    ) {}

    public function exportForDatabase(): DatabaseMessage
    {
        return new DatabaseMessage([
            'subject' => "Изменение дней городов в туре",
            'body' => "Изменение дней городов в туре",
            'data' => [
                'tour_id' => $this->tour->id
            ]
        ]);
    }
}