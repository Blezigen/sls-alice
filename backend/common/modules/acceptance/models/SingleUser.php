<?php

namespace common\modules\acceptance\models;

use common\modules\sender\providers\call\contracts\IPhoneCallReceiver;
use common\modules\sender\providers\sms\contracts\ISmsReceiver;
use yii\base\Model;

class SingleUser extends Model implements ISmsReceiver, IPhoneCallReceiver
{
    public $phone;

    public function getPhone()
    {
        return $this->phone;
    }

    public function canReceiveSms(): bool
    {
        return true;
    }
}
