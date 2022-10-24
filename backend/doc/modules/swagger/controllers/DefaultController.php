<?php

namespace doc\modules\swagger\controllers;

use common\AbstractController;
use common\models\Account;
use common\modules\permission\AccessControl;
use common\modules\permission\AccessRule;

class DefaultController extends AbstractController
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
                        'roles' => ['@'],
                        'actions' => ['index'],
                        'allow' => true,
                        'enforce' => [
                            'act' => 'view_doc',
                        ],
                    ],
                    [
                        'class' => AccessRule::class,
                        'roles' => ['@', '?'],
                        'actions' => ['oauth2-redirect'],
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($version = 'latest')
    {
        if ($version == 'latest') {
            $version = \Yii::$app->swagger->getCurrentVersion();
        }

        return $this->render('swagger/index', [
            'title' => \Yii::$app->swagger->getAppName(),
            'fileSwagger' => "/doc/swagger.json?version={$version}",
        ]);
    }

    public function actionOauth2Redirect()
    {
        $version = \Yii::$app->swagger->getCurrentVersion();

        return $this->render('swagger/oauth2-redirect', []);
    }
}
