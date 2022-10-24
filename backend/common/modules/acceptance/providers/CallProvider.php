<?php

namespace common\modules\acceptance\providers;

use common\modules\acceptance\AbstractAcceptanceProvider;

class CallProvider extends AbstractAcceptanceProvider
{
    public function getChannel()
    {
        return 'call';
    }
}
