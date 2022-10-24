<?php

namespace api\modules\auth\actions;

use api\modules\auth\forms\LoginForm;
use api\modules\auth\models\JwtAuthResult;
use common\exceptions\ValidationException;

class LoginAction extends \yii\base\Action
{
    public function run()
    {
        $model = new LoginForm(\Yii::$app->request->post());
        $result = $model->login();

        if (!$result) {
            throw new ValidationException($model->errors);
        }

        return ['data' => JwtAuthResult::make($result)];
    }
}
