<?php

namespace doc\modules\swagger\controllers;

use common\AbstractController;
use common\models\Account;
use common\modules\permission\AccessControl;
use common\modules\permission\AccessRule;
use yii\web\Response;

class FileController extends AbstractController
{
    public function behaviors()
    {
        return [
            'basicAuth' => [
                'class' => \yii\filters\auth\HttpBasicAuth::class,
                'realm' => 'doc',
                'auth' => function ($username, $password) {
                    $user = Account::findByUsername($username);
                    if ($user && $user->validatePassword($password)) {
                        return $user;
                    }

                    return null;
                },
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'class' => AccessRule::class,
                        'actions' => ['version'],
                        'allow' => true,
                        'enforce' => [
                            'act' => 'view_doc',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionVersion($type, $version = 'latest')
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;
        if (array_key_exists('application/json', \Yii::$app->request->acceptableContentTypes)) {
            \Yii::$app->response->headers->add('Content-type', 'application/json');

            return \Yii::$app->swagger->toJson($version);
        } elseif (array_key_exists('application/x-yaml', \Yii::$app->request->acceptableContentTypes)) {
            \Yii::$app->response->headers->add('Content-type', 'application/x-yaml');

            return \Yii::$app->swagger->toYaml($version);
        }

        if ($type == '.yaml') {
            \Yii::$app->response->headers->add('Content-type', 'application/x-yaml');

            return \Yii::$app->swagger->toYaml($version);
        }
        if ($type === '.json') {
            \Yii::$app->response->headers->add('Content-type', 'application/json');

            return \Yii::$app->swagger->toJson($version);
        }

        return \Yii::$app->swagger->toJson($version);
    }
}
