<?php

namespace common\actions;

use common\exceptions\ValidationException;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;

class CreateAction extends \yii\rest\CreateAction
{
    public $transformDataProvider;

    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $data = \Yii::$app->getRequest()->getBodyParams();

        if ($this->transformDataProvider !== null) {
            $data = call_user_func($this->transformDataProvider, $data);
        }

        $model->load($data, '');
        if (!$model->validate()) {
            throw new ValidationException($model->errors);
        }

        if ($model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        $model->refresh();

        return ["data" => $model];
    }
}
