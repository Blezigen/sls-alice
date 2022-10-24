<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class GeqFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'GEQ';

    /** @var mixed */
    private $param;

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['>=', $this->getAttribute($query->modelClass), $this->param]);
    }
}
