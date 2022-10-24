<?php

namespace common\modules\acceptance;

use common\contracts\IBootstrapSetting;
use common\modules\acceptance\contracts\IAcceptanceManager;
use common\modules\acceptance\providers\CallProvider;
use yii\base\BootstrapInterface;
use yii\console\Application;

class Module extends \yii\base\Module implements BootstrapInterface, IBootstrapSetting
{
    public $debug = false;

    public $controllerNamespace = 'common\modules\acceptance\controllers';

    protected $_migrations = [
        '@common/modules/acceptance/migrations',
    ];
    /**
     * @var mixed|object|null
     */
    public $serviceName = IAcceptanceManager::class;

    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, $config);
        $this->init();
    }

    public function getSettings()
    {
        return [
            SettingConstant::PLUGIN_SECTION => [
                'title' => 'Настройка плагина ACCEPTANCE',
                'description' => '',
                'settings' => [
                    SettingConstant::GENERATE_ATTEMPT_MAX => [
                        'title' => 'Максимальное количество попыток генерации кода',
                        'description' => 'Описание настройки',
                        'default' => 5,
                    ],

                    SettingConstant::CODE_ATTEMPT_MAX => [
                        'title' => 'Максимальное количество попыток ввода кода подтверждения',
                        'description' => 'Описание настройки',
                        'default' => 5,
                    ],

                    SettingConstant::DELAY => [
                        'title' => 'Задержка между попытками генерации кода',
                        'description' => 'Описание настройки',
                        'default' => 60,
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        if (\Yii::$app instanceof Application) {
            $this->controllerNamespace = "common\modules\acceptance\commands";
        }

        \Yii::$app->setComponents([
            $this->serviceName => [
                'class' => AcceptanceServices::class,
                'module' => $this,
                'providers' => [
                    'call' => [
                        'class' => CallProvider::class,
                    ],
                    'sms' => [
                        'class' => CallProvider::class,
                    ],
                ],
            ],
        ]);

        if (\Yii::$app instanceof Application) {
            \Yii::$app->controllerMap['migrate']['migrationPath'] = array_merge(
                \Yii::$app->controllerMap['migrate']['migrationPath'],
                $this->_migrations
            );
        }
    }

    public function bootstrap($app)
    {
    }
}
