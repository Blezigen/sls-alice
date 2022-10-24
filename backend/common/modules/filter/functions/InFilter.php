<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\contracts\IConvertMultipleSelected;

class InFilter extends AbstractFilterMethod implements IConvertMultipleSelected
{
    protected static string $keyWord = 'IN';

    protected array $list = [];

    public function __construct($list)
    {
        $this->list = $list;
    }

    protected static function convertParamsToConstructorParameters($params)
    {
        return [$params];
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['IN', $this->getAttribute($query->modelClass), $this->list]);
    }

    public function getSelected()
    {
        return $this->list;
    }
}
