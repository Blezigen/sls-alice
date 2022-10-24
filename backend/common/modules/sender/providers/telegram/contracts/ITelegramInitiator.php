<?php

namespace common\modules\sender\providers\telegram\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface ITelegramInitiator extends ISenderInitiator
{
    public function getMessage();
}
