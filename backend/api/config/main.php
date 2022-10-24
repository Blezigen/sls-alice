<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
);

$common = include __DIR__ . '/../../common/config/main.php';

return \yii\helpers\ArrayHelper::merge($common, [
    'id' => 'advanced-api',
    'basePath' => dirname(__DIR__),
    'layout' => false,
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => [
        'collection',
        'auth',
        \api\Bootstrap::class,
    ],
    'components' => [
        'request' => [
            'class' => \yii\web\Request::class,
            'trustedHosts' => [
                $_SERVER['SERVER_ADDR'],
            ],
            'cookieValidationKey' => 'nks-lkhjasgh45u1923yih',
            'baseUrl' => '/api',
            'parsers' => [
                'application/json+rpc-2.0' => \Redbox\JsonRpc\Parser::class,
                'application/json' => \yii\web\JsonParser::class,
                'text/xml' => \api\parsers\XmlParser::class,
                'application/xml' => \api\parsers\XmlParser::class,
                'multipart/form-data' => \yii\web\MultipartFormDataParser::class,
            ],
        ],
        'response' => [
            'class' => \yii\web\Response::class,
            'format' => \yii\web\Response::FORMAT_JSON,
            'formatters' => [
                'json+rpc-2.0' => [
                    'class' => \Redbox\JsonRpc\Formatter::class,
                    'prettyPrint' => YII_DEBUG,
                    // используем "pretty" в режиме отладки
                    'encodeOptions' => JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE,
                ],
                \yii\web\Response::FORMAT_XML => [
                    'class' => \yii\web\JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG,
                    // используем "pretty" в режиме отладки
                    'encodeOptions' => JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE,
                ],
                \yii\web\Response::FORMAT_JSON => [
                    'class' => \yii\web\JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG,
                    // используем "pretty" в режиме отладки
                    'encodeOptions' => JSON_UNESCAPED_SLASHES
                        | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'class' => \api\exceptions\Handler::class,
            'maxTraceSourceLines' => 2,
            //            'errorAction' => 'site/error',
        ],
    ],
    'modules' => [
        'auth' => [
            'class' => \api\modules\auth\Module::class,
        ],
        'collection' => [
            'class' => \api\modules\collection\Module::class,
        ],
    ],
    'params' => $params,
]);
