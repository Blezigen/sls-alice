<?php

namespace common\queries;

/**
 * This is the ActiveQuery class for [[\common\database\User]].
 *
 * @see \common\database\User
 */
class UserQuery extends \common\AbstractActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \common\database\User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \common\database\User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
