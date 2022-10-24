<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class EqnFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'EQN';

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['IS', $this->getAttribute($query->modelClass), null]);
    }
}
