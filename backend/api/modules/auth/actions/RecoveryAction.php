<?php

namespace api\modules\auth\actions;

use api\modules\auth\forms\RecoveryForm;

class RecoveryAction extends \yii\base\Action
{
    public function run()
    {
        $requestData = \Yii::$app->request->post();

        $form = new RecoveryForm($requestData);
        $result = $form->recovery();

        return ['data' => $result];
    }
}
