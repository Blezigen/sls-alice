<?php

namespace common\modules\permission;

use Casbin\CachedEnforcer;
use Casbin\Enforcer as SyncEnforcer;
use Casbin\Exceptions\CasbinException;

/**
 * Permission.
 *
 * @method bool enforce($userID, $module, $controller, $action, $act, $model, $attr)
 * @method bool hasRoleForUser($userID, $roleName)
 * @mixin SyncEnforcer
 */
class Permission extends \yii\permission\Permission
{
    public bool $enableCache = false;

    public function init()
    {
        $db = CasbinRule::getDb();
        $tableName = CasbinRule::tableName();
        $table = $db->getTableSchema($tableName);
        if (!$table) {
            $res = $db->createCommand()->createTable($tableName, [
                'id' => 'pk',
                'ptype' => 'string',
                'v0' => 'string',
                'v1' => 'string',
                'v2' => 'string',
                'v3' => 'string',
                'v4' => 'string',
                'v5' => 'string',
                'v6' => 'string',
                'v7' => 'string',
                'v8' => 'string',
                'v9' => 'string',
            ])->execute();
        }
    }

    /**
     * @throws CasbinException
     */
    public function enforcer($newInstance = false)
    {
        if ($newInstance || is_null($this->enforcer)) {
            $this->init();
            if ($this->enableCache)
                $this->enforcer = new CachedEnforcer($this->model, $this->adapter);
            else
                $this->enforcer = new SyncEnforcer($this->model, $this->adapter);
        }

        return $this->enforcer;
    }
}
