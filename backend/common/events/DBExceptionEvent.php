<?php

namespace common\events;

use yii\base\Event;
use yii\db\Exception;

class DBExceptionEvent extends Event
{
    /** @var Exception */
    public $exception;
}
