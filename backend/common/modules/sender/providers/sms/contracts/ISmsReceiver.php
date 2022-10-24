<?php

namespace common\modules\sender\providers\sms\contracts;

use common\modules\sender\contracts\ISenderReceiver;

interface ISmsReceiver extends ISenderReceiver
{
    public function getPhone();

    public function canReceiveSms(): bool;
}
