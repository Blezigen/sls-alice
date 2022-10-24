<?php

$api = include __DIR__ . '/../../api/config/main.php';

return \yii\helpers\ArrayHelper::merge($api, [
    'id' => 'test-api',
    'components' => [
        // <editor-fold desc="user">
        'user' => [
            'class' => \common\modules\permission\User::class,
            'identityClass' => \common\models\Account::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        // </editor-fold>

        // <editor-fold desc="db">
        'mutex' => [
            'class' => \yii\mutex\PgsqlMutex::class,
        ],

        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => implode(':', [
                'pgsql',
                implode(';', [
                    'host=' . env('DB_HOST', 'localhost'),
                    'port=' . env('DB_PORT', '5432'),
                    'dbname=' . 'postgres',
                ]),
            ]),
            'username' => 'postgres',
            'password' => 'postgres',
            'charset' => 'utf8',
        ],

        // </editor-fold>
    ],
]);
