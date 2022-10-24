<?php

namespace common\validators;

use yii\validators\Validator;

class OnlyOneValidator extends Validator
{
    public function validateAttribute($model, $attribute, $params = [])
    {
        $valid = false;

        foreach ($this->attributes as $attribute) {
            if (isset($model->$attribute) && $valid) {
                $valid = false;
                break;
            }
            if (isset($model->$attribute) && !$valid) {
                $valid = true;
            }
        }

        if (!$valid) {
            foreach ($this->attributes as $attribute) {
                if (isset($model->$attribute)) {
                    $this->addError($model, $attribute, 'Может быть заполнено только одно поле из [{fields}]', ['fields' => implode(',', $this->attributes)]);
                }
            }
        }
    }
}
