<?php

namespace common\modules\sender\providers\telegram;

use common\components\services\TelegramService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\providers\telegram\contracts\ITelegramInitiator;
use common\modules\sender\providers\telegram\contracts\ITelegramReceiver;
use common\modules\sender\SettingConstant;

class SenderProvider implements ISenderProvider
{
    /** @var ISenderComponent */
    public $service;

    /**
     * @param  ITelegramReceiver  $receiver
     * @param  ITelegramInitiator  $object
     */
    public function canBeProcessed($receiver, $object): bool
    {
        if (!$receiver instanceof ITelegramReceiver
            || !$object instanceof ITelegramInitiator
        ) {
            return false;
        }

        return true;
    }

    public function getTelegramToken()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::TELEGRAM_TOKEN,
            null
        );
    }

    /**
     * @param  ITelegramReceiver  $receiver
     * @param  ITelegramInitiator  $object
     *
     * @throws \Exception
     */
    public function execute($receiver, $object): bool
    {
        if (!$this->service->isEmulate()) {
            TelegramService::send(
                $receiver->getTelegramId(),
                $object->getMessage(),
                $this->getTelegramToken()
            );
        }

        if ($this->service->isDebug()) {
            $this->service->debug($object->getMessage(), 'telegram');
        }

        return true;
    }
}
