<?php

namespace common\actions;

use yii\web\NotFoundHttpException;

class ViewAction extends \yii\rest\ViewAction
{
    /**
     * Displays a model.
     *
     * @param string $id the primary key of the model
     *
     * @return \yii\db\ActiveRecordInterface the model being displayed
     */
    public function run($id)
    {
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (!$model) {
            throw new NotFoundHttpException('Не найдено');
        }

        return $model;
    }
}
