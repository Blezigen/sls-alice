<?php

namespace common\rbac;

use yii\rbac\Rule;

class OwnRule extends Rule
{
    public $name = 'isOwn';

    /**
     * @param string|int $user the user ID
     * @param Item $item the role or permission that this rule is associated width
     * @param array $params parameters passed to ManagerInterface::checkAccess()
     *
     * @return bool a value indicating whether the rule permits the role or permission it is associated with
     */
    public function execute($user, $item, $params)
    {
        return isset($params['model']) ? $params['model']->user_id == $user : false;
    }
}
