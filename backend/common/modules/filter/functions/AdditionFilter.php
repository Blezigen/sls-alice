<?php

namespace common\modules\filter\functions;

use Carbon\Carbon;
use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\contracts\IConvertMultipleSelected;
use common\modules\filter\contracts\IConvertRangeSelected;

class AdditionFilter extends AbstractFilterMethod implements IConvertRangeSelected
{
    protected static string $keyWord = 'ADDITION';

    /** @var mixed */
    private $p1;

    /** @var mixed */
    private $p2;

    /**
     * BtwFilter constructor.
     */
    public function __construct($p1, $p2)
    {
        $this->p1 = Carbon::parse($p1)->startOfDay();
        $this->p2 = Carbon::parse($p2)->endOfDay();
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $attributes = $this->getAttribute($query->modelClass);

        if (is_string($attributes))
            throw new \Exception(\Yii::t("app", "Переданное название атрибута является строкой"));
        if (!is_array($attributes) && count($attributes) < 2)
            throw new \Exception(\Yii::t("app", "Необходимо указать два атрибута для работы range фильтра"));

        list($minAttr, $maxAttr) = $attributes;

        $query->andWhere([
            "AND",
            [">=", "$minAttr", $this->p1],
            ["<=", "$minAttr", $this->p2],
            [">=", "$maxAttr", $this->p1],
            ["<=", "$maxAttr", $this->p2],
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