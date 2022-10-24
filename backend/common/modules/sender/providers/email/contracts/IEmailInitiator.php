<?php

namespace common\modules\sender\providers\email\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface IEmailInitiator extends ISenderInitiator
{
    public function getSubject();

    public function getMessage();
}
