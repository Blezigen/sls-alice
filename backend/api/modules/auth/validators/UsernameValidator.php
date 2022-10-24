<?php

namespace api\modules\auth\validators;

use api\modules\auth\models\User;
use yii\base\DynamicModel;
use yii\validators\ExistValidator;
use yii\validators\Validator;
use yii\web\HttpException;

class UsernameValidator extends Validator
{
    public $userClass = User::class;

    public function validateAttribute($model, $attribute, $params = [])
    {
        $model = DynamicModel::validateData([
            $attribute => $model->$attribute,
        ], [
            [
                ['username'],
                ExistValidator::class,
                'skipOnError' => false,
                'targetClass' => $this->userClass,
                'targetAttribute' => ['username' => 'username'],
                'message' => 'Пользователь не найден',
            ],
        ]);
        $model->validate();

        if ($model->hasErrors()) {
            throw new HttpException(403, 'Неверный логин или пароль');
        }
    }
}
