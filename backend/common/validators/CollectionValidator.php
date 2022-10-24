<?php

namespace common\validators;

use common\models\Collection;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

class CollectionValidator extends Validator
{
    public $collection = null;

    public function validateAttribute($model, $attribute, $params = [])
    {
        $collection = Collection::find()->collection($this->collection);
        $collectionExist = clone $collection;
        if (!$collectionExist->slug($model->$attribute)->exists()) {
            $list = ArrayHelper::getColumn($collection->all(),
                function ($value) {
                    return "'$value->slug'";
                });
            $list = implode(', ', $list);
            $model->addError($this->collection, "Мета поле только одно из следующих значений: $list");
        }
    }
}
