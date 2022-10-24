<?php

namespace common\behaviors;

use yii\base\Behavior;
use yii\base\Model;
use yii\db\ActiveRecord;

class PhoneFormatBehavior extends Behavior
{
    public $attribute = 'phone';

    public function events()
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        if (property_exists($event->sender, $this->attribute)) {
            /** @var ActiveRecord $model */
            $model = $event->sender;
            $phone = $model->{$this->attribute};

            if (isset($phone)) {
                $phone = preg_replace("/[^\d]+/", '', $phone);

                if (mb_strlen($phone) < 11) {
                    $model->addError($this->attribute, 'Телефон содержит мало цифр!');
                }

                if (mb_strlen($phone) > 11) {
                    $model->addError($this->attribute, 'Телефон содержит содержит больше 11 цифр!');
                }

                $phone = preg_replace("/(.*)([\d]{10})$/", '$2', $phone);
                $phone = "7{$phone}";
                $event->sender->{$this->attribute} = $phone;
            }
        }
    }
}
