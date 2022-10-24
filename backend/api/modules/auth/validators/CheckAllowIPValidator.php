<?php

namespace api\modules\auth\validators;

use api\modules\auth\exceptions\IPCheckingException;
use api\modules\auth\models\User;
use api\modules\auth\SettingConstant;
use yii\validators\Validator;

class CheckAllowIPValidator extends Validator
{
    public $collection = null;
    public $userClass = User::class;

    public function checkIPSetting($model, $attribute)
    {
        /** @var User $user */
        $user = $this->userClass::findByUsername($model->$attribute);

        return $user->getSetting(
            \api\modules\auth\SettingConstant::PLUGIN_SECTION,
            SettingConstant::REQUIRED_IP_CHECK,
            false
        );
    }

    public function validateAttribute($model, $attribute, $params = [])
    {
        if (!$this->checkIPSetting($model, $attribute)) {
            return;
        }
        throw new IPCheckingException('Запрещён доступ с текущего IP.');
//        Yii::$app->request->userIP;
//        $allowed = UserAllowedIp::find()
//            ->select('value')
//            ->andWhere([
//                "AND",
//                ['user_id' => $user->getId()],
//                ['is_active' => 1],
//                ["LIKE", "value", $ip]
//            ])
//            ->exists();
//
//        if (!$allowed)
//            throw new \Exception("Disallowed IP");
    }
}
