<?php

namespace common\validators;

use yii\validators\Validator;

class OneMoreValidator extends Validator
{
    public function validateAttribute($model, $attribute, $params = [])
    {
        $valid = false;

        foreach ($this->attributes as $attribute) {
            if (isset($model->$attribute) && !$valid) {
                $valid = true;
            }
        }

        if (!$valid) {
            foreach ($this->attributes as $attribute) {
                if (isset($model->$attribute)) {
                    $this->addError($model, $attribute, 'Должно быть заполнено одно поле или больше поле из [{fields}]', ['fields' => implode(',', $this->attributes)]);
                }
            }
        }
    }
}
