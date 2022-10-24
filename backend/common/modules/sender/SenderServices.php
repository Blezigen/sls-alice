<?php

namespace common\modules\sender;

use common\components\services\TelegramService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderInitiator;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\contracts\ISenderReceiver;
use yii\base\Model;

class SenderServices extends Model implements ISenderComponent
{
    /**
     * @var ISenderProvider[]|array
     */
    public $providers = [];

    /**
     * @var Module
     */
    public $module;

    public function isDebug()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::DEBUG,
            $this->module->debug
        );
    }

    public function isEmulate()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::EMULATE,
            $this->module->emulate
        );
    }

    public function getEmulateTelegramChatId()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::EMULATE_TELEGRAM_CHAT_ID,
            null
        );
    }

    public function getEmulateTelegramToken()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::EMULATE_TELEGRAM_TOKEN,
            null
        );
    }

    public function notify(ISenderReceiver $receiver, ISenderInitiator $object)
    {
        foreach ($this->providers as $key => $provider) {
            $provider = \Yii::createObject($provider);

            if (!$provider instanceof ISenderProvider) {
                \Yii::debug('Класс ' . get_class($provider) . ' не имеет интерфейса ' . ISenderProvider::class);
                continue;
            }

            if (property_exists($provider, 'service')) {
                $provider->service = $this;
            }

            if (!$provider->canBeProcessed($receiver, $object)) {
                continue;
            }

            try {
                if ($provider->execute($receiver, $object)) {
                    \Yii::debug('Успешно отправлен объект ' . get_class($object));
                }
            } catch (\Throwable $ex) {
                \Yii::debug($ex->getMessage());
                throw $ex;
            }
        }
    }

    public function debug($message, $category = 'default')
    {
        TelegramService::send(
            $this->getEmulateTelegramChatId(),
            "--- === Debug ($category) === ---\n" . $message,
            $this->getEmulateTelegramToken()
        );
    }
}
