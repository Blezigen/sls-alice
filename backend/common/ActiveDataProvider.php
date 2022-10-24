<?php

namespace common;

use yii\base\InvalidConfigException;
use yii\db\QueryInterface;

class ActiveDataProvider extends \yii\data\ActiveDataProvider
{

    public ?SkipTake $skipTake = null;

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;

        if ($this->skipTake) {
            $query->offset($this->skipTake->skip)->limit($this->skipTake->take);
        } elseif (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }

        if (($sort = $this->getSort()) !== false) {
            $query->addOrderBy($sort->getOrders());
        }

        if (\Yii::$app->request->get('debug', false) === 'query'
            || \Yii::$app->request->headers->get('x-debug', false) === 'query'
        ) {
            dd($query->createCommand()->rawSql, $query->createCommand()->params);
        }

        return $query->all($this->db);
    }

}