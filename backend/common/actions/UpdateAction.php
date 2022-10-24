<?php

namespace common\actions;

use common\exceptions\ValidationException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\ServerErrorHttpException;

class UpdateAction extends \yii\rest\UpdateAction
{
    /**
     * @var string the scenario to be assigned to the model before it is validated and updated
     */
    public $scenario = Model::SCENARIO_DEFAULT;

    /**
     * Updates an existing model.
     *
     * @param string $id the primary key of the model
     *
     * @return \yii\db\ActiveRecordInterface the model being updated
     *
     * @throws ServerErrorHttpException if there is any error when updating the model
     */
    public function run($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->scenario = $this->scenario;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $model->validate();

        if ($model->hasErrors()) {
            throw new ValidationException($model->errors);
        }

        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }
}
