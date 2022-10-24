<?php

namespace common\modules\acceptance\notifications;

use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use common\modules\sender\providers\sms\contracts\ISmsCodeInitiator;
use common\modules\sender\providers\sms\contracts\ISmsReceiver;
use yii\base\Model;

class SendSmsCodeInitiator extends Model implements ISmsCodeInitiator
{
    public $generateAttempt = 1;

    public function getCode()
    {
        return (string) random_int(1000, 9999);
    }

    public function getMessage()
    {
        return "Код {$this->getCode()}";
    }

    public function saveSmsCode(ISmsReceiver $receiver, $code)
    {
        /** @var IAcceptanceManager|AcceptanceServices $service */
        $service = \Yii::$app->get(IAcceptanceManager::class);
        $old = $service->getAcceptance($receiver->getPhone(), IAcceptanceManager::SMS);
        $new = $service->createAcceptance($receiver->getPhone(), $code, IAcceptanceManager::SMS);
        $new->generate_attempt_count = $this->generateAttempt;

        if ($old) {
            $old->delete();
        }
    }
}
