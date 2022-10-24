<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class GeFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'GE';

    /** @var mixed */
    private $param;

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['>', $this->getAttribute($query->modelClass), $this->param]);
    }
}
