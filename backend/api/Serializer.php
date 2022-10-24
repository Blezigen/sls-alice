<?php

namespace api;

use yii\base\Arrayable;
use yii\data\DataProviderInterface;
use yii\web\Application;

class Serializer extends \yii\rest\Serializer
{
    public $expandParam = 'extend';

    public array $expandParams = [];

    /**
     * @return array the names of the requested fields. The first element is an array
     * representing the list of default fields requested, while the second element is
     * an array of the extra fields requested in addition to the default fields.
     *
     * @see Model::fields()
     * @see Model::extraFields()
     */
    protected function getRequestedFields()
    {
        $fields = \Yii::$app instanceof Application ? $this->request->get($this->fieldsParam) : [];
        $expand = \Yii::$app instanceof Application ? $this->request->get($this->expandParam) : [];

        $expand = array_unique(array_merge($this->expandParams, $expand));

        return [
            is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1,
                PREG_SPLIT_NO_EMPTY) : [],
            is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1,
                PREG_SPLIT_NO_EMPTY) : [],
        ];
    }

    /**
     * Serializes a data provider.
     *
     * @param  DataProviderInterface  $dataProvider
     *
     * @return array the array representation of the data provider
     */
    protected function serializeDataProvider($dataProvider)
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }

        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if (\Yii::$app instanceof Application) {
            if ($this->request->getIsHead()) {
                return null;
            } elseif ($this->collectionEnvelope === null) {
                return $models;
            }
        }

        $result = [
            $this->collectionEnvelope => $models,
        ];
        if ($pagination !== false) {
            return array_merge($result,
                $this->serializePagination($pagination));
        }

        return $result;
    }

    /**
     * Serializes a model object.
     *
     * @param  Arrayable  $model
     *
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if (\Yii::$app instanceof Application) {
            if ($this->request->getIsHead()) {
                return null;
            }
        }

        list($fields, $expand) = $this->getRequestedFields();

        return $model->toArray($fields, $expand);
    }
}
