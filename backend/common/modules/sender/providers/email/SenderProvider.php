<?php

namespace common\modules\sender\providers\email;

use common\components\services\MailService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\providers\call\contracts\IPhoneCallInitiator;
use common\modules\sender\providers\call\contracts\IPhoneCallReceiver;
use common\modules\sender\providers\email\contracts\IEmailInitiator;
use common\modules\sender\providers\email\contracts\IEmailReceiver;

class SenderProvider implements ISenderProvider
{
    /** @var ISenderComponent */
    public $service;

    /**
     * @param  IEmailReceiver  $receiver
     * @param  IEmailInitiator  $object
     */
    public function canBeProcessed($receiver, $object): bool
    {
        if (!$receiver instanceof IEmailReceiver || !$object instanceof IEmailInitiator) {
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
        $emails = $receiver->getEmails();
        $mess = $object->getMessage();
        $subject = $object->getSubject();

        if (!$this->service->isEmulate()) {
            foreach ($emails as $email) {
                MailService::send($email, $subject, $mess);
            }
        }

        if ($this->service->isDebug()) {
            $message = [
                'Отправлено электронное сообщение',
                "Заголовок: $subject",
                //                "Сообщение: $mess",
                "Почта получателя: ".implode(",", $emails),
            ];
            $this->service->debug(implode("\n", $message), 'email');
        }

        return true;
    }
}
