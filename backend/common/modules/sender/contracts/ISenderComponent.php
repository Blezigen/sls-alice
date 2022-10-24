<?php

namespace common\modules\sender\contracts;

interface ISenderComponent
{
    public function isEmulate();

    public function isDebug();

    public function notify(ISenderReceiver $receiver, ISenderInitiator $object);

    public function debug($message, $category = 'default');
}
