<?php

namespace common\modules\acceptance\commands;

use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use console\AbstractConsoleController;

/**
 * Подтверждение номер телефона, а конкретно модуль для проверки подтверждения.
 */
class TestController extends AbstractConsoleController
{
    public $type = IAcceptanceManager::SMS;

    public function options($actionID)
    {
        return ['type'];
    }

    public function optionAliases()
    {
        return ['t' => 'type'];
    }

    /**
     * Отправка кода в зависимости от переменной (--type | -t)
     *
     * @param $phone
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSend($phone)
    {
        /** @var AcceptanceServices $manager */
        $manager = \Yii::$app->get(IAcceptanceManager::class);
        $manager->sendCode($phone, $this->type);
    }

    /**
     * Получение токена по коду в зависимости от переменной (--type | -t)
     *
     * @param $phone
     * @param $code
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionToken($phone, $code)
    {
        /** @var AcceptanceServices $manager */
        $manager = \Yii::$app->get(IAcceptanceManager::class);
        $token = $manager->getAcceptTokenOnCode($phone, $code, $this->type);
        $this->output->writeln("token: <green>$token</green>");
    }

    public function actionUse($phone, $token)
    {
        /** @var AcceptanceServices $manager */
        $manager = \Yii::$app->get(IAcceptanceManager::class);
        $token = $manager->useAcceptToken($phone, $token, $this->type);
        $this->output->writeln("token: <green>$token</green>");
    }
}
