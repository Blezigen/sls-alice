<?php

namespace api\modules\auth;

use api\modules\auth\models\User;
use common\contracts\IBootstrapSetting;
use Redbox\PersonalSettings\SettingAccess;
use Redbox\PersonalSettings\SettingSummary;

class Module extends \common\AbstractModule implements IBootstrapSetting
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\auth\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        \Yii::$app->user->enableSession = true;
    }

    public function bootstrap($app)
    {
//        $app->user->identityClass = User::class;
        parent::bootstrap($app);
    }

    public function routes($moduleID)
    {
        return [
            // <editor-fold desc="site">
            'POST oauth2/<action:\w+>' => 'auth/site/<action>',
            // </editor-fold>
            // <editor-fold desc="auth">

            'POST auth/jwt/login'    => 'auth/jwt/login',
            'POST auth/jwt/refresh'  => 'auth/jwt/refresh',
            'POST auth/jwt/recovery' => 'auth/jwt/recovery',
            'POST auth/jwt/revoke'   => 'auth/jwt/revoke',

            'OPTIONS auth/jwt/login'    => 'auth/jwt/options',
            'OPTIONS auth/jwt/refresh'  => 'auth/jwt/options',
            'OPTIONS auth/jwt/recovery' => 'auth/jwt/options',
            'OPTIONS auth/jwt/revoke'   => 'auth/jwt/options',

            'GET,HEAD oauth/login'         => 'auth/site/login',
            'GET,HEAD oauth/authorize'     => 'auth/site/authorize',
            'POST,HEAD oauth/token'         => 'auth/site/token',
            'GET,HEAD oauth/refresh-token' => 'auth/site/refresh-token',

            'GET auth'              => 'auth/default/login-view',
            'POST auth'             => 'auth/default/login',
            'POST auth/login'       => 'auth/default/login',
            'POST auth/logout'      => 'auth/default/logout',
            'POST oauth/login'      => 'auth/o-auth/login',

            // </editor-fold>
            // <editor-fold desc="registrations">
            'POST auth/remember'    => "{$moduleID}/remember/create",
            'OPTIONS auth/remember' => "{$moduleID}/remember/options",
            // </editor-fold>
        ];
    }

    public function getSettings()
    {
        return [
            SettingConstant::PLUGIN_SECTION => [
                'title'       => 'Настройка авторизации',
                'description' => 'Описание секции',
                'settings'    => [
                    SettingConstant::REQUIRED_TWO_FACTOR => [
                        'title'       => 'Включить двухфакторную авторизацию',
                        'description' => 'Описание настройки',
                        'default'     => false,
                        'values'      => [
                            [
                                'value'   => false,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['admin']])
                                ]),
                            ],
                        ],
                    ],
                    SettingConstant::REQUIRED_IP_CHECK   => [
                        'title'       => 'Включить ограничение входа по IP',
                        'description' => 'Описание настройки',
                        'default'     => false,
                    ],
                ],
            ],
        ];
    }
}
