<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;

class LikeFilter extends AbstractFilterMethod
{
    protected static string $keyWord = 'LIKE';

    private string $stringQuery;

    public function __construct($stringQuery)
    {
        $this->stringQuery = $stringQuery;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere(['LIKE', $this->getAttribute($query->modelClass), $this->stringQuery]);
    }
}
