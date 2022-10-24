<?php

namespace api\modules\users\controllers;

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;

class DeviceController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    [
                        'class' => QueryParamAuth::className(),
                        'tokenParam' => 'accessToken',
                    ],
                ],
                'optional' => [
                ],
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className(),
            ],
        ]);
    }

    public function actionAction()
    {
        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');
        $data = \Yii::$app->request->post();
        $devices = $data['payload']['devices'];
        foreach ($devices as $device) {
            if ($device['id'] === 'uniq_1') {
                $capabilities = $device['capabilities'];
                $type = $capabilities[0]['type'];
                $state = $capabilities[0]['state']['value'];
                file_get_contents('http://92.255.198.79/api/scripts?action=evalFile&path=/alice.lua');
            }
        }

        return [
            'request_id' => $requestId,
            'payload' => [
                'devices' => [
                    [
                        'id' => 'uniq_1',
                        'capabilities' => [
                            [
                                'type' => 'devices.capabilities.on_off',
                                'state' => [
                                    'instance' => 'on',
                                    'action_result' => [
                                        'status' => 'DONE',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionQuery()
    {
        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');

        return [
            'request_id' => $requestId,
            'payload' => [
                'devices' => [
                    [
                        'id' => 'uniq_1',
                        'capabilities' => [
                            [
                                'type' => 'devices.capabilities.on_off',
                                'state' => [
                                    'instance' => 'on',
                                    'value' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');

        return [
            'request_id' => $requestId,
            'payload' => [
                'user_id' => "$userId",
                'devices' => [
                    [
                        'id' => 'uniq_1',
                        'name' => 'Выключатель',
                        'description' => 'умная Выключатель',
                        'room' => 'спальня',
                        'type' => 'devices.types.switch',
                        'custom_data' => [
                            'api_location' => 'rus',
                        ],
                        'capabilities' => [
                            [
                                'type' => 'devices.capabilities.on_off',
                                'retrievable' => false,
                                'reportable' => false,
                                'parameters' => [
                                    'split' => false,
                                ],
                            ],
                        ],
                        'properties' => [
                        ],
                        'device_info' => [
                            'manufacturer' => 'SLS devices',
                            'model' => 'custom',
                            'hw_version' => 'v1.0',
                            'sw_version' => 'v1.0',
                        ],
                    ],
                ],
            ],
        ];
    }
}
