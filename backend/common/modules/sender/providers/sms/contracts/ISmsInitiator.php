<?php

namespace common\modules\sender\providers\sms\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface ISmsInitiator extends ISenderInitiator
{
    public function getMessage();
}
