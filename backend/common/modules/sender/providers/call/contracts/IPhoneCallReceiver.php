<?php

namespace common\modules\sender\providers\call\contracts;

use common\modules\sender\contracts\ISenderReceiver;

interface IPhoneCallReceiver extends ISenderReceiver
{
    public function getPhone();
}
