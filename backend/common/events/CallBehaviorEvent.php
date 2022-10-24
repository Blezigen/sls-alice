<?php

namespace common\events;

use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class CallBehaviorEvent extends Event
{
    /** @var ActiveQuery|ActiveRecord */
    public $sender;
    public $method;
    public $params;
}
