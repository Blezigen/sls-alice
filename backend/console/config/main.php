<?php

use console\modules\generator\generators\database_model\schemas\PgsqlSchema;

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'console\controllers',
    'bootstrap' => [
        'auth',
        'install',
        \console\Bootstrap::class,
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => \console\controllers\FixtureController::class,
            'fixtureDataPath' => '@tests/_data',
            'globalFixtures' => [
                \tests\InitDbFixture::class,
            ],
            'templatePath' => '@tests/fixtures',
            'namespace' => 'tests\fixtures',
        ],
        'seeder' => [
            'class' => \antonyz89\seeder\SeederController::class,
        ],
        'migrate' => [
            'class' => \console\controllers\MigrateController::class,
            'migrationPath' => [
                '@console/migrations',
                //                '@yii/rbac/migrations',
                '@common/modules/webhook_module/migrations',
                '@common/modules/acceptance/migrations',
                //                '@vendor/yii2mod/yii2-settings/migrations',
                '@vendor/rdbx/yii2-user-setting/src/migrations',
            ],
        ],
        'pstorage' => [
            'class' => \console\controllers\PStorageController::class,
            'migrationPath' => [
                '@console/spocedures',
            ],
        ],
        'migration' => [
            'class' => 'bizley\migration\controllers\MigrationController',
        ],
    ],
    'components' => [
        'schedule' => [
            'class' => \omnilight\scheduling\Schedule::class,
        ],

        'db' => [
            'schemaMap' => [
                'pgsql' => PgsqlSchema::class,
            ],
        ],
        'urlManager' => [
            'baseUrl' => env('API_URL'),
        ],
        // <editor-fold desc="errorHandler">
        'errorHandler' => [
            'class' => \console\exceptions\ConsoleHandler::class,
            'maxTraceSourceLines' => 5,
        ],
        // </editor-fold>
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'except' => ['Mintrans'],
                ],
                [
                    // yii migrate --migrationPath=@yii/log/migrations/
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'logTable' => '{{%integration_log}}',
                    'categories' => ['Mintrans'],
                ],
            ],
        ],

        //        'urlManager' => [],
    ],
    'params' => $params,
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'dataPath' => '@root/api/runtime/debug',
        ],
        'auth' => [
            'class' => \api\modules\auth\Module::class,
        ],
        'generator' => [
            'class' => \console\modules\generator\Module::class,
        ],
        'install' => [
            'class' => \console\modules\install\Module::class,
        ],
        'swagger' => [
            'class' => \doc\modules\swagger\Module::class,
        ],
    ],
];
