<?php

namespace common\modules\acceptance\notifications;

use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use common\modules\sender\providers\call\contracts\IPhoneCallInitiator;
use common\modules\sender\providers\call\contracts\IPhoneCallReceiver;
use yii\base\Model;

class PhoneCallInitiator extends Model implements IPhoneCallInitiator
{
    public $generateAttempt = 1;

    public function savePhoneCallCode(IPhoneCallReceiver $receiver, $code)
    {
        /** @var AcceptanceServices $service */
        $service = \Yii::$app->get(AcceptanceServices::class);
        $old = $service->getAcceptance($receiver->getPhone(), IAcceptanceManager::CALL);

        $new = $service->createAcceptance($receiver->getPhone(), $code, IAcceptanceManager::CALL);
        $new->generate_attempt_count = $this->generateAttempt;
        $new->save();

        if ($old) {
            $old->delete();
        }
    }
}
