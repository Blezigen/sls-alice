<?php

namespace api\modules\auth\forms;

use api\modules\auth\validators\TwoFactorValidator;
use api\modules\auth\validators\UsernameValidator;
use common\BaseModel;
use common\exceptions\ValidationException;
use yii\web\NotFoundHttpException;

class RecoveryForm extends BaseModel
{
    public $username;
    public $accept_token;
    public $password;

    private $_user;

    public static function __docAttributeExample()
    {
        return [
            'accept_token' => '{token}',
            'username' => '79274361277',
            'password' => 'tester',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'accept_token'], 'required'],
            [
                ['username'],
                UsernameValidator::class,
            ],
            [
                ['accept_token'],
                TwoFactorValidator::class,
            ],
        ];
    }

    public function recovery()
    {
        $user = \api\modules\auth\models\User::findByUsername($this->username);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }

        if (!$this->validate()) {
            throw new ValidationException($this->errors);
        }

        $user->setPassword($this->password);
        $user->save();

        return true;
    }
}
