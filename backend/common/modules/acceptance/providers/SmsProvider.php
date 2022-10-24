<?php

namespace common\modules\acceptance\providers;

use common\modules\acceptance\AbstractAcceptanceProvider;

class SmsProvider extends AbstractAcceptanceProvider
{
    public function getChannel()
    {
        return 'sms';
    }
}
