<?php

namespace integration\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        return $behaviors;
    }

    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            return [
                // 'name' => $exception->getName(),
                'message' => $exception->getMessage(),
                'code' => 0,
                'status' => $exception->statusCode,
            ];
        }

        return $exception;
    }
}
