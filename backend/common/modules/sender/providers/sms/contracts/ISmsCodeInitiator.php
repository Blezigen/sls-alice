<?php

namespace common\modules\sender\providers\sms\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface ISmsCodeInitiator extends ISenderInitiator
{
    public function getCode();

    public function getMessage();

    public function saveSmsCode(ISmsReceiver $receiver, $code);
}
