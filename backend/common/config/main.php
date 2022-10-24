<?php

use Redbox\PersonalSettings\components\SettingProvider;
use Redbox\PersonalSettings\components\Settings;

$config = [
    'language' => 'ru-RU',
    'sourceLanguage' => 'ru-RU',
    'timeZone' => 'Europe/Moscow',

    // <editor-fold desc="bootstrap">
    'bootstrap' => [
        'log',
        'acquiring',
        'queue',
        'webhook_module',
        'senderModule',
        'oauth2',
        'acceptance',
        \common\Bootstrap::class,
    ],
    // </editor-fold>
    // <editor-fold desc="bootstrap">
    'container' => [
        'singletons' => [
            \common\modules\filter\FilterQueryParser::class => [
                'class' => \common\modules\filter\FilterQueryParser::class,
                'requestFilterParam' => 'filter',
                'filterClasses' => [
                    'addition' => \common\modules\filter\functions\AdditionFilter::class,
                    'byperiod' => \common\modules\filter\functions\ByPeriodFilters::class,
                    'neq' => \common\modules\filter\functions\NeqFilter::class,
                    'eqn' => \common\modules\filter\functions\EqnFilter::class,
                    'neqn' => \common\modules\filter\functions\NeqnFilter::class,
                    'geq' => \common\modules\filter\functions\GeqFilter::class,
                    'ge' => \common\modules\filter\functions\GeFilter::class,
                    'leq' => \common\modules\filter\functions\LeqFilter::class,
                    'le' => \common\modules\filter\functions\LeFilter::class,
                    'eq' => \common\modules\filter\functions\EqFilter::class,
                    'like' => \common\modules\filter\functions\LikeFilter::class,
                    'btw' => \common\modules\filter\functions\BtwFilter::class,
                    'in' => \common\modules\filter\functions\InFilter::class,
                ],
            ],
        ],
    ],
    // </editor-fold>
    // <editor-fold desc="aliases">
    'aliases' => [
        '@cdn' => env('STORAGE_PUBLIC_URL'),
        '@frontend_system' => env('FRONTEND_SYSTEM_URL'),
        '@frontend' => env('FRONTEND_URL'),
        '@web' => '',
        '@public_default' => '@cdn/default',
        '@public' => '@cdn',
        '@storage' => '@app/../../storage',
        '@root' => '@vendor/../../backend',

        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@doc' => '@root/doc/web',
        '@tests' => '@root/tests',
    ],
    // </editor-fold>

    // <editor-fold desc="vendorPath">
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    // </editor-fold>

    // <editor-fold desc="components">
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [],
        ],

        // <editor-fold desc="permission">
        'permission' => [
            'class' => \common\modules\permission\Permission::class,

            'enableCache' => true,

            // Casbin model setting.
            'model' => [
                // Available Settings: "file", "text"
                'config_type' => 'file',
                'config_file_path' => __DIR__ . '/casbin-model.conf',
                'config_text' => '',
            ],

            // Casbin adapter.
            'adapter' => \common\modules\permission\Adapter::class,

            // Casbin database setting.
            'database' => [
                // Database connection for following tables.
                'connection' => 'db',
                // CasbinRule tables and model.
                'casbin_rules_table' => '{{%permission_rules}}',
            ],
        ],
        // </editor-fold>

        // <editor-fold desc="settings">
        'settings' => [
            'class' => \Redbox\PersonalSettings\components\SettingComponent::class,
            'provider' => SettingProvider::class,
        ],
        // </editor-fold>

        // <editor-fold desc="errorHandler">
        'errorHandler' => [
            'class' => \common\exceptions\Handler::class,
            'maxTraceSourceLines' => 100,
        ],
        // </editor-fold>

        // <editor-fold desc="authManager">
        'authManager' => [
            'class' => \common\rbac\DbManager::class,
            'assignmentTable' => 'auth_assignment',
            'defaultRoles' => ['guest'],
        ],
        // </editor-fold>

        // <editor-fold desc="user">
        'user' => [
            'class' => \common\modules\permission\User::class,
            'identityClass' => \common\models\User::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        // </editor-fold>

        // <editor-fold desc="session">
        'session' => [
            'class' => \yii\web\Session::class,
            'name' => 'advanced-api',
        ],
        // </editor-fold>

        // <editor-fold desc="cache">
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        // </editor-fold>

        // <editor-fold desc="db">
        'mutex' => [
            'class' => \yii\mutex\PgsqlMutex::class,
        ],

        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => implode(':', [
                env('DB_DRIVER'),
                implode(';', [
                    'host=' . env('DB_HOST'),
                    'port=' . env('DB_PORT', '3306'),
                    'dbname=' . env('DB_DATABASE'),
                ]),
            ]),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 600,
            'schemaCache' => 'cache',
            'enableQueryCache' => true,
            'queryCacheDuration' => 3600,
        ],

        // </editor-fold>

        // <editor-fold desc="mailer">
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => env('SMTP_HOST'),
                'username' => env('SMTP_USERNAME'),
                'password' => env('SMTP_PASSWORD'),
                'port' => env('SMTP_PORT'),
                'encryption' => 'ssl',
            ],
        ],

        // </editor-fold>

        'redis' => [
            'class' => \yii\redis\Connection::class,
            // ...
        ],

        // <editor-fold desc="queue">
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
//            'db' => 'db',
            // Компонент подключения к БД или его конфиг
//            'tableName' => '{{%queue}}',
            // Имя таблицы
            'channel' => 'germes-notification',
            // Выбранный для очереди канал
            //            'mutex' => \yii\mutex\PgsqlMutex::class,
            //            'mutex'                        => \yii\mutex\MysqlMutex::class,
            // Мьютекс для синхронизации запросов
            'as log' => \yii\queue\LogBehavior::class,

            'on ' . \common\Queue::EVENT_AFTER_ERROR => function (
                yii\queue\ExecEvent $event
            ) {
                (new \console\exceptions\QueueHandler())->handle($event->job,
                    $event->error);
            },
        ],
        // </editor-fold>

        'notifier' => [
            'class' => \common\modules\notification\Notifier::class,
            'channels' => [
                'database' => [
                    'class' => \common\modules\notification\ActiveRecordChannel::class,
                ],
            ],
        ],
    ],
    // </editor-fold>

    'modules' => [
        'acquiring' => [
            'class' => \common\modules\acquiring\Module::class,
        ],
        'webhook_module' => [
            'class' => \common\modules\webhook_module\Module::class,
        ],

        'senderModule' => [
            'class' => \common\modules\sender\Module::class,
            'serviceName' => 'sender',
            'emulate' => true,
            'debug' => true,
        ],

        'acceptance' => [
            'class' => \common\modules\acceptance\Module::class,
            'debug' => true,
        ],

        'oauth2' => [
            'class' => \filsh\yii2\oauth2server\Module::class,
            'tokenParamName' => 'access-token',
            'useJwtToken' => true,
            'tokenAccessLifetime' => 3600 * 24,
            'components' => [
                'request' => function () {
                    return \filsh\yii2\oauth2server\Request::createFromGlobals();
                },
                'response' => [
                    'class' => \filsh\yii2\oauth2server\Response::class,
                ],
            ],
            'storageMap' => [
                'user_credentials' => \common\models\Account::class,
                'public_key' => \common\PublicKeyStorage::class,
                'access_token' => \OAuth2\Storage\JwtAccessToken::class,
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => \OAuth2\GrantType\UserCredentials::class,
                ],
                'refresh_token' => [
                    'class' => \OAuth2\GrantType\RefreshToken::class,
                    'always_issue_new_refresh_token' => true,
                ],
            ],
            'options' => [
                'allow_implicit' => true,
                'require_exact_redirect_uri' => false,
            ],
        ],
    ],
];

if (YII_DEBUG) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'traceLine' => '<a href="phpstorm://open?url={file}&line={line}">{file}:{line}</a>',
        // uncomment and adjust the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '78.138.147.154', '*'],
        'panels' => [
            'user' => false,
        ],
    ];
}

return $config;
