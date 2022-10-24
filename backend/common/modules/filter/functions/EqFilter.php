<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\contracts\IConvertSingleSelected;

class EqFilter extends AbstractFilterMethod implements IConvertSingleSelected
{
    protected static string $keyWord = 'EQ';

    /** @var mixed */
    private $param;

    public function getSelected()
    {
        return $this->param;
    }

    public function __construct($param)
    {
        $this->param = $param;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['=', $this->getAttribute($query->modelClass), $this->param]);
    }
}
