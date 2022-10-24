<?php

namespace common\modules\webhook_module\controllers;

use common\AbstractController;
use common\models\Account;
use common\models\User;
use yii\web\Response;

class Controller extends AbstractController
{
    public function init()
    {
        parent::init();
        $this->layout = 'main';
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        return array_merge($behaviors, [
            'basicAuth' => [
                'class' => \yii\filters\auth\HttpBasicAuth::class,
                'auth' => function ($username, $password) {
                    $user = User::findByUsername($username);
                    if ($user && $user->validatePassword($password)) {
                        return $user;
                    }

                    return null;
                },
            ],
        ]);
    }

    public function afterAction($action, $result)
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;

        return parent::afterAction($action,
            $result); 
    }
}
