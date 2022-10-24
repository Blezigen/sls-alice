<?php

namespace common\modules\sender;

use common\contracts\IBootstrapSetting;
use common\modules\sender\contracts\ISenderComponent;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface, IBootstrapSetting
{
    public $serviceName = ISenderComponent::class;

    public $emulate = false;

    public $debug = false;

    public $controllerNamespace = 'console\modules\sender\controllers';

    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, $config);
        $this->init();
    }

    public function init()
    {
        \Yii::$app->setComponents([
                $this->serviceName => [
                    'class' => SenderServices::class,
                    'providers' => [
                        [
                            'class' => providers\call\SenderProvider::class,
                        ],
                        [
                            'class' => providers\email\SenderProvider::class,
                        ],
                        [
                            'class' => providers\sms\SenderProvider::class,
                        ],
                        [
                            'class' => providers\telegram\SenderProvider::class,
                        ],
                        [
                            'class' => providers\unisender\SenderProvider::class,
                        ],
                    ],
                    'module' => $this,
                ],
        ]);
        parent::init();
    }

    public function bootstrap($app)
    {
    }

    public function getSettings()
    {
        return [
            SettingConstant::PLUGIN_SECTION => [
                'title' => 'Настройка рассылающего плагина',
                'description' => 'Настройка аккаунтов через которые происходит рассылка',
                'settings' => [
                    SettingConstant::DEBUG => [
                        'title' => 'Включить отладку плагина',
                        'description' => 'Описание настройки',
                        'default' => true,
                    ],

                    SettingConstant::EMULATE => [
                        'title' => 'Включить эмуляцию плагина',
                        'description' => 'Описание настройки',
                        'default' => true,
                    ],

                    SettingConstant::EMULATE_TELEGRAM_TOKEN => [
                        'title' => 'TELEGRAM-токен режима эмуляции',
                        'description' => 'Эмуляция отправки сообщений по telegram каналу',
                        'default' => '5314229610:AAEYkWQ4EQzrXoeavTghpPuB_VdptSLqBb0',
                    ],

                    SettingConstant::EMULATE_TELEGRAM_CHAT_ID => [
                        'title' => 'TELEGRAM-токен режима эмуляции',
                        'description' => 'Эмуляция отправки сообщений по telegram каналу',
                        'default' => '-1001747581175',
                    ],

                    SettingConstant::UNISENDER_TOKEN => [
                        'title' => 'UNISENDER-токен',
                        'description' => '',
                        'default' => '{{unisender_token}}',
                    ],

                    SettingConstant::UNISENDER_EMAIL => [
                        'title' => 'UNISENDER-email',
                        'description' => '',
                        'default' => 'example@email.com',
                    ],

                    SettingConstant::UNISENDER_SENDER_NAME => [
                        'title' => 'UNISENDER-имя отправителя',
                        'description' => '',
                        'default' => 'rdbx',
                    ],

                    SettingConstant::TELEGRAM_TOKEN => [
                        'title' => 'TELEGRAM-токен',
                        'description' => '',
                        'default' => false,
                    ],
                ],
            ],
        ];
    }
}
