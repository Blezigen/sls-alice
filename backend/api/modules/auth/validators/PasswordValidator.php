<?php

namespace api\modules\auth\validators;

use yii\validators\Validator;
use yii\web\IdentityInterface;

class PasswordValidator extends Validator
{
    /** @var IdentityInterface */
    public $userClass = \api\modules\auth\models\User::class;
    public $username = 'username';

    public function validateAttribute($model, $attribute, $params = [])
    {
        if (!$model->hasErrors()) {
            $user = $this->userClass::findByUsername($model->{$this->username});
//            dd($model->$attribute);
            if (!$user->validatePassword($model->$attribute)) {
                $this->addError($model, $attribute, 'Неверный логин или пароль');
            }
        }
    }
}
