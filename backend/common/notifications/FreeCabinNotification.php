<?php

namespace common\notifications;

use common\models\Cabin;
use common\modules\sender\providers\email\contracts\IEmailInitiator;
use yii\base\Model;

class FreeCabinNotification implements IEmailInitiator
{
    public Cabin $cabin;

    public function __construct($cabin)
    {
        $this->cabin = $cabin;
    }

    public function getSubject(){
        return "Уведомление об освобождении каюты #{$this->cabin->number}";
    }
    public function getMessage(){
        return "Уведомление об освобождении каюты #{$this->cabin->number}";
    }
}