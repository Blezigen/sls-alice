<?php

abstract class ApplicationPlaceholders
{
    public \Redbox\PersonalSettings\components\SettingComponent $settings;
    public \common\modules\sender\contracts\ISenderComponent $sender;
    public \common\modules\permission\User $user;
    public \common\modules\permission\Permission $permission;
    public \yii\queue\Queue $queue;
    public \doc\modules\swagger\SwaggerManager $swagger;
    public \common\modules\notification\Notifier $notifier;
}

class Yii extends \yii\BaseYii
{
    /** @var \yii\console\Application|\yii\web\Application|\yii\base\Application|ApplicationPlaceholders * */
    public static $app;

    /** @inheritdoc  */
    public static $container;
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require __DIR__ . '/vendor/yiisoft/yii2/classes.php';
Yii::$container = new yii\di\Container();
