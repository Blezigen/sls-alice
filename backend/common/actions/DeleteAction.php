<?php

namespace common\actions;

use api\exceptions\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use Yii;

class DeleteAction extends \yii\rest\DeleteAction
{
    /**
     * Deletes a model.
     *
     * @param  mixed  $id  id of the model to be deleted
     *
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException on failure
     * @throws \yii\web\NotFoundHttpException
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (!$model) {
            throw new NotFoundHttpException(\Yii::t('app', 'Entity id={id} not found', ['id' => $id]));
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }



        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        Yii::$app->getResponse()->setStatusCode(200);

        return [
            "data" => [
                "message" => "Успешно удалено"
            ]
        ];
    }
}
