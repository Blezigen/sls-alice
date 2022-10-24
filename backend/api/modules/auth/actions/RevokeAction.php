<?php

namespace api\modules\auth\actions;

use api\modules\auth\forms\RevokeForm;
use common\exceptions\ValidationException;

class RevokeAction extends \yii\base\Action
{
    public function run()
    {
        $model = new RevokeForm();

        $model->load($this->request->post(), '');

        if ($model->errors) {
            throw new ValidationException($model->errors);
        }

        $result = $model->revoke();

        return [
            'data' => $result,
        ];
    }
}
