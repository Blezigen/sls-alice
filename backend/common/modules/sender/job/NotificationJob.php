<?php

namespace common\modules\sender\job;

use common\modules\sender\contracts\ISenderInitiator;
use common\modules\sender\contracts\ISenderReceiver;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class NotificationJob extends BaseObject implements JobInterface
{
    public ISenderInitiator $initiator;
    public ISenderReceiver $receiver;

    public function __construct(ISenderInitiator $initiator, ISenderReceiver $receiver, $config = [])
    {
        $this->initiator = $initiator;
        $this->receiver = $receiver;

        parent::__construct($config);
    }


    public function execute($queue)
    {
        $sender = \Yii::$app->sender;
        $sender->notify($this->receiver, $this->initiator);

        return true;
    }
}
