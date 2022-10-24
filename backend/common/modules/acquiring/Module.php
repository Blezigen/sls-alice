<?php

namespace common\modules\acquiring;

use api\helpers\UrlRule;
use common\contracts\IBootstrapSetting;
use common\modules\acceptance\contracts\IAcceptanceManager;
use common\modules\acquiring\providers\AlfaAcquiringProvider;
use common\modules\acquiring\providers\DefaultAcquiringProvider;
use common\modules\acquiring\providers\PsbAcquiringProvider;
use common\modules\acquiring\providers\SberbankAcquiringProvider;
use common\modules\acquiring\services\QrCodeAcquiringProvider;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface, IBootstrapSetting
{
    public $debug = false;

    public $controllerNamespace = 'common\modules\acquiring\controllers';

    protected $_migrations = [
        '@common/modules/acquiring/migrations',
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
                'title' => 'Настройка плагина ACQUIRING',
                'description' => '',
                'settings' => [
                    SettingConstant::PSB_COMP1 => [
                        'title' => 'Первая компонента ключа',
                        'description' => 'Описание настройки',
                        'default' => 'C50E41160302E0F5D6D59F1AA3925C45',
                    ],

                    SettingConstant::PSB_COMP2 => [
                        'title' => 'Вторая компонента ключа',
                        'description' => 'Описание настройки',
                        'default' => '00000000000000000000000000000000',
                    ],

                    SettingConstant::PSB_MERCH_NAME => [
                        'title' => 'Название магазина',
                        'description' => 'Описание настройки',
                        'default' => 'TEST_MERCH',
                    ],

                    SettingConstant::PSB_TERMINAL => [
                        'title' => 'Номер терминала',
                        'description' => 'Описание настройки',
                        'default' => 79036777,
                    ],

                    SettingConstant::PSB_MERCHANT => [
                        'title' => 'Номер терминала',
                        'description' => 'Описание настройки',
                        'default' => 790367686219999,
                    ],

                    SettingConstant::PSB_URL => [
                        'title' => 'URL для промышленной среды',
                        'description' => 'Описание настройки',
                        'default' => 'https://test.3ds.payment.ru/cgi-bin/payment_ref/generate_payment_ref',
                    ],

                    SettingConstant::PSB_URL_STATUS => [
                        'title' => 'URL для проверки платежа',
                        'description' => 'Описание настройки',
                        'default' => 'https://test.3ds.payment.ru/cgi-bin/check_operation/ecomm_check',
                    ],

                    SettingConstant::PSB_HOST => [
                        'title' => 'HOST для промышленной среды',
                        'description' => 'Описание настройки',
                        'default' => 'test.3ds.payment.ru',
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        \Yii::$container->setSingletons([
            PsbAcquiringProvider::PROVIDER => [
                'class' => PsbAcquiringProvider::class,
            ],
            AlfaAcquiringProvider::PROVIDER => [
                'class' => AlfaAcquiringProvider::class,
            ],
            SberbankAcquiringProvider::PROVIDER => [
                'class' => SberbankAcquiringProvider::class,
            ],
            QrCodeAcquiringProvider::PROVIDER => [
                'class' => QrCodeAcquiringProvider::class,
            ],
            DefaultAcquiringProvider::PROVIDER => [
                'class' => DefaultAcquiringProvider::class,
            ],
        ]);

//
//        if (\Yii::$app instanceof Application) {
//            $this->controllerNamespace = "common\modules\acceptance\commands";
//        }
//
//        \Yii::$app->setComponents([
//            $this->serviceName => [
//                'class' => AcceptanceServices::class,
//                'module' => $this,
//                'providers' => [
//                    'call' => [
//                        'class' => CallProvider::class,
//                    ],
//                    'sms' => [
//                        'class' => CallProvider::class,
//                    ],
//                ],
//            ],
//        ]);
//
//        if (\Yii::$app instanceof Application) {
//            \Yii::$app->controllerMap['migrate']['migrationPath'] = array_merge(
//                \Yii::$app->controllerMap['migrate']['migrationPath'],
//                $this->_migrations
//            );
//        }
    }

    public function bootstrap($app)
    {
        $moduleID = $this->id;
        $app->urlManager->addRules([
            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/test/default/<id>',
                'route' => "{$moduleID}/default/test",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/qr-code/<filename>',
                'route' => "{$moduleID}/default/file",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/<provider>/notify',
                'route' => "{$moduleID}/acquiring/notify",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/<provider>/<id>/pay',
                'route' => "{$moduleID}/acquiring/pay",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/<provider>/<id>/success',
                'route' => "{$moduleID}/acquiring/success",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/<provider>/<id>/fail',
                'route' => "{$moduleID}/acquiring/fail",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/<provider>/<id>/back',
                'route' => "{$moduleID}/acquiring/back",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/psb/<id>',
                'route' => "{$moduleID}/psb/pay",
            ],

            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/psb/payback/<id>',
                'route' => "{$moduleID}/psb/backref",
            ],
            [
                'class' => UrlRule::class,
                'pattern' => 'acquiring/psb/payback',
                'route' => "{$moduleID}/psb/payback",
            ],
        ]);
    }
}
