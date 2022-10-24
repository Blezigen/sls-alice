<?php

namespace common\modules\sender\providers\telegram\contracts;

use common\modules\sender\contracts\ISenderReceiver;

interface ITelegramReceiver extends ISenderReceiver
{
    public function getTelegramId();

    public function canReceiveTelegram(): bool;
}
