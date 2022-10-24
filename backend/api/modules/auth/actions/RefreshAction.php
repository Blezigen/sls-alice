<?php

namespace api\modules\auth\actions;

use api\modules\auth\forms\RefreshForm;
use api\modules\auth\models\JwtAuthResult;
use common\exceptions\ValidationException;

class RefreshAction extends \yii\base\Action
{
    public function run()
    {
        $model = new RefreshForm();

        $model->load(\Yii::$app->request->post(), '');

        $data = $model->refresh();

        if ($model->errors) {
            throw new ValidationException($model->errors);
        }

        $result = new JwtAuthResult($data);

        return [
            'data' => $result,
        ];
    }
}
