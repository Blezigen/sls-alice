<?php

namespace common\modules\sender\contracts;

interface ISenderProvider
{
    /**
     * @param  ISenderReceiver  $receiver
     * @param  ISenderInitiator  $object
     */
    public function canBeProcessed($receiver, $object): bool;

    /**
     * @param  ISenderReceiver  $receiver
     * @param  ISenderInitiator  $object
     */
    public function execute($receiver, $object): bool;
}
