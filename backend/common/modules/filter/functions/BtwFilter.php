<?php

namespace common\modules\filter\functions;

use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\contracts\IConvertRangeSelected;

class BtwFilter extends AbstractFilterMethod implements IConvertRangeSelected
{
    protected static string $keyWord = 'BTW';

    /** @var mixed */
    private $p1;

    /** @var mixed */
    private $p2;

    /**
     * BtwFilter constructor.
     */
    public function __construct($p1, $p2)
    {
        $this->p1 = $p1;
        $this->p2 = $p2;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $query->andWhere([
            'AND',
            ['>=', $this->getAttribute($query->modelClass), $this->p1],
            ['<=', $this->getAttribute($query->modelClass), $this->p2],
        ]);
    }

    public function getSelectedMin()
    {
        return $this->p1;
    }

    public function getSelectedMax()
    {
        return $this->p2;
    }
}
