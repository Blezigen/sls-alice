<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class NeqnFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'NEQN';

    public function __construct()
    {
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['IS NOT', $this->getAttribute($query->modelClass), null]);
    }
}
