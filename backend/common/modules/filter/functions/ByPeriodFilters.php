<?php

namespace common\modules\filter\functions;

use Carbon\Carbon;
use common\models\CustomPeriod;
use common\modules\filter\AbstractFilterMethod;
use common\modules\filter\contracts\IConvertRangeSelected;

class ByPeriodFilters extends AbstractFilterMethod implements IConvertRangeSelected
{
    protected static string $keyWord = 'BYPERIOD';

    /** @var mixed */
    private $p1;

    /** @var mixed */
    private $p2;

    /**
     * @var bool
     */
    private $year = true;

    /**
     * BtwFilter constructor.
     */
    public function __construct($id)
    {
        $period = CustomPeriod::find()
            ->withoutTrashed()
            ->byId($id)
            ->one();

        if (!$period) {
            throw new \Exception(\Yii::t('app', "Не найден {$id} периода"));
        }

        $this->p1 = $period->start_at;
        $this->p2 = $period->end_at;

        $this->year = $period->is_check_year;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        $attribute = $this->getAttribute($query->modelClass);

        if ($this->year) {
            $query->andWhere([
                'AND',
                ['>=', "$attribute", $this->p1],
                ['<=', "$attribute", $this->p2],
            ]);
        } else {
            $m = Carbon::create($this->p1)->format('m');
            $d = Carbon::create($this->p1)->format('d');
            $year = Carbon::now()->format('Y');
            $this->p1 = Carbon::createFromDate($year, $m, $d)->format('Y-m-d H:i:s');

            $m = Carbon::create($this->p2)->format('m');
            $d = Carbon::create($this->p2)->format('d');
            $year = Carbon::now()->format('Y');
            $this->p2 = Carbon::createFromDate($year, $m, $d)->format('Y-m-d H:i:s');

            $query->andWhere([
                'AND',
                ['>=', "$attribute", $this->p1],
                ['<=', "$attribute", $this->p2],
            ]);
        }
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
