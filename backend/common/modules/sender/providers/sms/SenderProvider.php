<?php

namespace common\modules\sender\providers\sms;

use common\components\services\SMSCService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\providers\sms\contracts\ISmsCodeInitiator;
use common\modules\sender\providers\sms\contracts\ISmsInitiator;
use common\modules\sender\providers\sms\contracts\ISmsReceiver;

class SenderProvider implements ISenderProvider
{
    /** @var ISenderComponent */
    public $service;

    /**
     * @param  ISmsReceiver  $receiver
     * @param  ISmsInitiator|ISmsCodeInitiator  $object
     */
    public function canBeProcessed($receiver, $object): bool
    {
        if (!$receiver instanceof ISmsReceiver) {
            return false;
        }

        if (!$receiver instanceof ISmsInitiator
            && !$object instanceof ISmsCodeInitiator
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param  ISmsReceiver  $receiver
     * @param  ISmsInitiator|ISmsCodeInitiator  $object
     *
     * @throws \Exception
     */
    public function execute($receiver, $object): bool
    {
        if (!$this->service->isEmulate()) {
            SMSCService::send($receiver->getPhone(), $object->getMessage());
        }

        if ($this->service->isDebug()) {
            if ($object instanceof ISmsCodeInitiator) {
                $code = $object->getCode();
                $message = "Отправлен код подтверждения `$code` на телефон `{$receiver->getPhone()}`";
                $this->service->debug($message, 'sms');
            }
            if ($object instanceof ISmsInitiator) {
                $this->service->debug($object->getMessage(), 'sms');
            }
        }

        if ($object instanceof ISmsCodeInitiator) {
            $code = $object->getCode();
            $object->saveSmsCode($receiver, $code);
        }

        return true;
    }
}
