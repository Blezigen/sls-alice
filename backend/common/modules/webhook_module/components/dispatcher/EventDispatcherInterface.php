<?php

namespace common\modules\webhook_module\components\dispatcher;

use common\modules\webhook_module\models\Webhook;
use yii\base\Event;

interface EventDispatcherInterface
{
    public function dispatch(Event $event, Webhook $webhook);
}
