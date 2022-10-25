<?php

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
);

return [
    'id' => 'advanced-doc',
    'basePath' => dirname(__DIR__),
    'layout' => false,
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => [
        'swagger',
        'auth',
        'collection',
        \doc\Bootstrap::class,
    ],
    'components' => [
        'request' => [
            'class' => \yii\web\Request::class,
            'cookieValidationKey' => 'nks-lkhjasgh45u1923yih',
            'baseUrl' => '/doc',
            'parsers' => [
                'application/json+rpc-2.0' => [
                    'class' => \yii\web\JsonParser::class,
                    'asArray' => false,
                ],
                'application/json' => \yii\web\JsonParser::class,
                'text/xml' => \api\parsers\XmlParser::class,
                'application/xml' => \api\parsers\XmlParser::class,
                'multipart/form-data' => \yii\web\MultipartFormDataParser::class,
            ],
        ],
        'response' => [
            'class' => \yii\web\Response::class,
        ],
        'errorHandler' => [
            'class' => \doc\exceptions\Handler::class,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [],
        ],
        // <editor-fold desc="session">
        'session' => [
            'class' => \yii\web\Session::class,
            'name' => 'advanced-doc',
        ],
        // </editor-fold>
    ],
    'modules' => [
        'auth' => [
            'class' => \api\modules\auth\Module::class,
        ],
        'tool' => [
            'class' => \api\modules\tool\Module::class,
        ],
        'collection' => [
            'class' => \api\modules\collection\Module::class,
        ],
        'swagger' => [
            'class' => \doc\modules\swagger\Module::class,
        ],
        'account' => [
            'class' => \api\modules\account\Module::class,
        ],
        'period' => [
            'class' => \api\modules\period\Module::class,
        ],
        'tour_module' => [
            'class' => \api\modules\tour_module\Module::class,
        ],
        'setting_module' => [
            'class' => \api\modules\setting_module\Module::class,
        ],
        'identity_document_module' => [
            'class' => \api\modules\identity_document_module\Module::class,
        ],
        'fleet_module' => [
            'class' => \api\modules\fleet_module\Module::class,
        ],
        'discount_module' => [
            'class' => \api\modules\discount_module\Module::class,
        ],
        'contractor_module' => [
            'class' => \api\modules\contractor_module\Module::class,
        ],
        'order_module' => [
            'class' => \api\modules\order_module\Module::class,
        ],
        'exec_module' => [
            'class' => \api\modules\exec_module\Module::class,
        ],
        'ship_module' => [
            'class' => \api\modules\ship_module\Module::class,
        ],
    ],
    'params' => $params,
];
