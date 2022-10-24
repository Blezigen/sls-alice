<?php

namespace common\modules\sender\providers\call;

use common\components\services\SMSCService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\providers\call\contracts\IPhoneCallInitiator;
use common\modules\sender\providers\call\contracts\IPhoneCallReceiver;

class SenderProvider implements ISenderProvider
{
    /** @var ISenderComponent */
    public $service;

    /**
     * @param  IPhoneCallReceiver  $receiver
     * @param  IPhoneCallInitiator  $object
     */
    public function canBeProcessed($receiver, $object): bool
    {
        if (!$receiver instanceof IPhoneCallReceiver || !$object instanceof IPhoneCallInitiator) {
            return false;
        }

        return true;
    }

    /**
     * @param  IPhoneCallReceiver  $receiver
     * @param  IPhoneCallInitiator  $object
     *
     * @throws \Exception
     */
    public function execute($receiver, $object): bool
    {
        $code = (string) random_int(1111, 9999);

        if (!$this->service->isEmulate()) {
            $code = SMSCService::call($receiver->getPhone());
        }

        if ($this->service->isDebug()) {
            $message = "Отправлен запрос звонка на телефон `{$receiver->getPhone()}`, получен код `$code`";
            $this->service->debug($message, 'call');
        }

        if ($code === null) {
            throw new \Exception('Не удалось получить код для подтверждения звонка');
        }

        $object->savePhoneCallCode($receiver, $code);

        return true;
    }
}
