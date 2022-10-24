<?php

namespace common\validators;

use common\models\Collection;
use yii\helpers\ArrayHelper;
use yii\validators\Validator;

class CollectionIdValidator extends Validator
{
    public $collection = null;

    public function validateAttribute($model, $attribute, $params = [])
    {
        $collection = Collection::find()->collection($this->collection);
        $collectionExist = clone $collection;

        if (!$collectionExist->id($model->$attribute)->exists()) {
            $list = ArrayHelper::getColumn($collection->all(),
                function ($value) {
                    return "'$value->slug'[$value->id]";
                });
            $list = implode(', ', $list);
            $model->addError($attribute, "Возможно заполнение только одним из следующих значений из коллекции({$this->collection}): $list");
        }
    }
}
