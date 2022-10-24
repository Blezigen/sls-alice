<?php

namespace common\modules\sender;

use common\modules\sender\contracts\ISenderReceiver;
use common\modules\sender\providers\call\contracts\IPhoneCallReceiver;
use common\modules\sender\providers\email\contracts\IEmailReceiver;
use common\modules\sender\providers\sms\contracts\ISmsReceiver;
use common\modules\sender\providers\telegram\contracts\ITelegramReceiver;
use common\modules\sender\providers\unisender\contracts\IUnisenderReceiver;
use yii\base\Model;

class SingleReceiver extends Model implements ISenderReceiver, IUnisenderReceiver, IEmailReceiver, ITelegramReceiver, ISmsReceiver, IPhoneCallReceiver
{
    public $email;
    public $phone;
    public $telegram;

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getTelegramId()
    {
        return $this->telegram;
    }

    public function getEmails()
    {
        return [$this->email];
    }

    public function canReceiveSms(): bool
    {
        return true;
    }

    public function canReceiveTelegram(): bool
    {
        return true;
    }

    public function canReceiveEmail(): bool
    {
        return true;
    }
}
