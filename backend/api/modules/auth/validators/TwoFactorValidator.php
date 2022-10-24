<?php

namespace api\modules\auth\validators;

use api\modules\auth\exceptions\TwoFactorRequiredException;
use api\modules\auth\SettingConstant;
use common\modules\acceptance\behaviors\AcceptTokenBehavior;
use common\modules\acceptance\validators\AcceptTokenValidator;
use yii\base\DynamicModel;
use yii\validators\Validator;
use yii\web\IdentityInterface;

class TwoFactorValidator extends Validator
{
    /** @var IdentityInterface */
    public $userClass = \api\modules\auth\models\User::class;
    public $phoneAttribute = 'username';
    public $acceptTokenAttribute = 'accept_token';

    public function requiredTwoFactor($model, $attribute)
    {
        /** @var Account $user */
        $user = $this->userClass::findByUsername($model->$attribute);

        return $user->getSetting(
            \api\modules\auth\SettingConstant::PLUGIN_SECTION,
            SettingConstant::REQUIRED_TWO_FACTOR,
            false
        );
    }

    public function validateAttributes($model, $attributes = null)
    {
        if ($this->requiredTwoFactor($model, $this->phoneAttribute)) {
            $model->attachBehaviors([
                [
                    'class' => AcceptTokenBehavior::class,
                    'phoneAttribute' => 'username',
                ],
            ]);

            $entity = DynamicModel::validateData([
                $this->phoneAttribute => $model->{$this->phoneAttribute}
                    ?? null,
                $this->acceptTokenAttribute => $model->{$this->acceptTokenAttribute}
                    ?? null,
            ], [
                [$this->acceptTokenAttribute, 'required', 'strict' => true],
                [$this->phoneAttribute, 'required', 'strict' => true],
                [
                    $this->phoneAttribute,
                    AcceptTokenValidator::class,
                    'phoneAttribute' => $this->phoneAttribute,
                ],
            ]);
            \Yii::debug('Проверка на наличие ошибок при валидации.',
                get_class($this));

            if (!$entity->validate()) {
                \Yii::debug('Токен не прошёл проверку валидации. '
                    . json_encode($entity->errors), get_class($this));
                throw new TwoFactorRequiredException('Передайте атрибут двух-факторной авторизации');
            }
        }
    }
}
