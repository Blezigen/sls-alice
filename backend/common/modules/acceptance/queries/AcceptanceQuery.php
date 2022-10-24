<?php

namespace common\modules\acceptance\queries;

/**
 * This is the ActiveQuery class for [[\common\database\Acceptance]].
 *
 * @see \common\database\Acceptance
 */
class AcceptanceQuery extends \common\AbstractActiveQuery
{
    public function channel($value)
    {
        return $this->andWhere(['channel' => $value]);
    }

    public function phone($value)
    {
        return $this->andWhere(['phone' => $value]);
    }

    public function notUsed()
    {
        return $this->andWhere(['is', 'used_at', null]);
    }

    /**
     * {@inheritdoc}
     *
     * @return \common\database\Acceptance[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     *
     * @return \common\database\Acceptance|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
