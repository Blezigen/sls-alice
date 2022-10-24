<?php

namespace common\modules\sender\providers\call\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface IPhoneCallInitiator extends ISenderInitiator
{
    public function savePhoneCallCode(IPhoneCallReceiver $receiver, $code);
}
