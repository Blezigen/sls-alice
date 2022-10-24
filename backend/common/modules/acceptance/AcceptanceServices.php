<?php

namespace common\modules\acceptance;

use Carbon\Carbon;
use common\modules\acceptance\contracts\IAcceptanceManager;
use common\modules\acceptance\contracts\IAcceptanceProvider;
use common\modules\acceptance\models\Acceptance;
use common\modules\sender\contracts\ISenderProvider;
use yii\base\Model;

class AcceptanceServices extends Model implements IAcceptanceManager
{
    /**
     * @var Module
     */
    public $module;

    public $codeAttemptMax = 10;

    public $generateAttemptMax = 3;

    public $delay = 60;

    public $providers = [];

    public function isDebug()
    {
        return $this->module->debug;
    }

    public function dropAllExpired()
    {
        Acceptance::deleteAll([
            'and',
            [
                '<',
                'expired_at',
                Carbon::now()->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            ],
            [
                'is',
                'used_at',
                null,
            ],
        ]);
    }

    public function getGenerateAttemptMax()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::GENERATE_ATTEMPT_MAX,
            $this->generateAttemptMax
        );
    }

    public function getCodeAttemptMax()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::CODE_ATTEMPT_MAX,
            $this->codeAttemptMax
        );
    }

    public function getDelay()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::DELAY,
            $this->delay
        );
    }

    protected function getProvider($type = self::SMS): IAcceptanceProvider
    {
        if (!array_key_exists($type, $this->providers)) {
            throw new \Exception('Канал для отправки не найден!');
        }

        $temp = &$this->providers[$type];
        $temp['generateAttemptMax'] = $this->getGenerateAttemptMax();
        $temp['codeAttemptMax'] = $this->getCodeAttemptMax();
        $temp['delay'] = $this->getDelay();

        $provider = \Yii::createObject($this->providers[$type]);

        if (!$provider instanceof IAcceptanceProvider) {
            throw new \Exception('Класс ' . get_class($provider) . ' не имеет интерфейса ' . ISenderProvider::class);
        }

        return $provider;
    }

    public function createAcceptance(
        $phone,
        $code,
        $type = self::SMS
    ): Acceptance {
        $provider = $this->getProvider($type);

        return $provider->createAcceptance($phone, $code);
    }

    public function getAcceptance($phone, $type = self::SMS): ?Acceptance
    {
        $provider = $this->getProvider($type);

        return $provider->getAcceptance($phone);
    }

    public function sendCode($phone, $type = self::SMS)
    {
        $provider = $this->getProvider($type);

        return $provider->sendCode($phone);
    }

    public function hasAcceptToken($phone, $token, $type = self::SMS)
    {
        $provider = $this->getProvider($type);

        return $provider->hasAcceptance($phone, $token);
    }

    public function useAcceptToken($phone, $token, $type = self::SMS)
    {
        $provider = $this->getProvider($type);

        return $provider->useAcceptance($phone, $token);
    }

    public function getAcceptTokenOnCode($phone, $code, $type = self::SMS)
    {
        $provider = $this->getProvider($type);

        return $provider->getAcceptOnCode($phone, $code);
    }
}
