<?php

namespace common\modules\sender\providers\email\contracts;

use common\modules\sender\contracts\ISenderReceiver;

interface IEmailReceiver extends ISenderReceiver
{
    public function getEmail();

    public function canReceiveEmail(): bool;
}
