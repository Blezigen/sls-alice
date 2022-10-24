<?php

namespace common\actions;

use common\BaseActiveRecord;
use yii\rest\Action;

class MetaActions extends Action
{
    /**
     * @param string $id the primary key of the model
     *
     * @return array
     */
    public function run($id)
    {
        /** @var BaseActiveRecord $model */
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        return $model->getMeta();
    }
}
