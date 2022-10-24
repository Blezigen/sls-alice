<?php

$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php',
    require __DIR__ . '/../../common/config/params.php',
);

$common = include __DIR__ . '/../../common/config/main.php';

// return \yii\helpers\ArrayHelper::merge($common, [
return [
    'id' => 'integration-germes',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'integration\controllers',
    'layout' => false,
    'aliases' => [
        '@integration-cdn' => dirname(__DIR__) . '/cdn',
    ],
    'bootstrap' => [
        'queue',
    ],
    'container' => [
        'singletons' => [
            \common\contracts\IOrderService::class => [
                'class' => \common\components\services\OrderService::class,
            ],
            \common\components\services\ShipService::class => [
                'class' => \common\components\services\ShipService::class,
            ],
            \common\TourService::class => [
                'class' => \common\TourService::class,
            ],
            \common\modules\filter\FilterQueryParser::class => [
                'class' => \common\modules\filter\FilterQueryParser::class,
                'requestFilterParam' => 'filter',
                'filterClasses' => [
                    'addition' => \common\modules\filter\functions\AdditionFilter::class,
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
            \common\modules\metaInfo\MetadataService::class => [
                'class' => \common\modules\metaInfo\MetadataService::class,
                'tables' => [],
            ],
            \common\components\services\TourService::class => \common\components\services\TourService::class,
            \common\DiscountService::class => \common\DiscountService::class,
        ],
    ],
    'components' => [
        'user' => [
            'class' => \common\modules\permission\User::class,
            'identityClass' => \common\models\Account::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],
        'request' => [
            'class' => \yii\web\Request::class,
            'cookieValidationKey' => 'germes-lkhjasgh45u1923yih',
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
            ],
        ],
        'response' => [
            'class' => \yii\web\Response::class,
            'format' => yii\web\Response::FORMAT_JSON,

            'formatters' => [
                \yii\web\Response::FORMAT_JSON => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // use "pretty" output in debug mode
                    'encodeOptions' =>
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'default/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'rules' => [],
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
        ],
        'queue' => [
            'class' => \yii\queue\file\Queue::class,
            'path' => '@runtime/queue',
            // 'attempts' => 10,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ]
    ],
    'params' => $params,
];
