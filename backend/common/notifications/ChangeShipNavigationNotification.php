<?php

namespace common\notifications;

use common\models\ShipNavigation;
use tuyakhov\notifications\messages\DatabaseMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;
use Yii;

class ChangeShipNavigationNotification implements NotificationInterface
{
    use NotificationTrait;

//    public function __construct(
//        private ShipNavigation $shipNavigation
//    ) {}

    public function exportForDatabase()
    {
        return Yii::createObject([
            'class' => DatabaseMessage::class,
            'subject' => "Изменение дней городов в туре",
            'body' => "",
            'data' => [
                'actionUrl' => ['href' => '/invoice/123/view', 'label' => 'View Details']
            ]
        ]);
    }
}