<?php

namespace common\modules\acceptance\validators;

use api\modules\auth\exceptions\TwoFactorRequiredException;
use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use yii\validators\Validator;

class AcceptTokenValidator extends Validator
{
    public $phoneAttribute = 'phone';
    public $acceptTokenAttribute = 'accept_token';

    public function validateAttribute($model, $attribute, $params = [])
    {
        /** @var IAcceptanceManager|AcceptanceServices $service */
        $service = \Yii::$app->get(IAcceptanceManager::class);
//        dd($model->$attribute, $model->{$this->attribute});
        $data = $service->hasAcceptToken($model->{$this->phoneAttribute}, $model->{$this->acceptTokenAttribute});

        if (!$data) {
            throw new TwoFactorRequiredException('Не найден двух-факторный код');
        }
    }
}
