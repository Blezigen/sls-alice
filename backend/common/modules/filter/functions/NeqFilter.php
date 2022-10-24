<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class NeqFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'NEQ';

    /** @var mixed */
    private $param;

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['not', [$this->getAttribute($query->modelClass) => $this->param]]);
    }
}
