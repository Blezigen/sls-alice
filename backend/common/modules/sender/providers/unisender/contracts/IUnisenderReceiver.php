<?php

namespace common\modules\sender\providers\unisender\contracts;

use common\modules\sender\contracts\ISenderReceiver;

interface IUnisenderReceiver extends ISenderReceiver
{
    public function getEmails();

    public function canReceiveEmail(): bool;
}
