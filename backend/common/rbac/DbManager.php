<?php

namespace common\rbac;

use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;

class DbManager extends \yii\rbac\DbManager
{
    public function assign($role, $userId)
    {
        // Проверка, есть ли у юзера хотя бы одна роль.
        if (!empty($this->getRolesByUser($userId))) {
            throw new \Exception(['Нельзя присваивать пользователю более одной роли.']);
        }

        return parent::assign($role, $userId);
    }

    public string $fieldIdName = 'user_id';

    public function removeAllRolesFromEntityId($entityId)
    {
        $this->db->createCommand()
            ->delete($this->assignmentTable,
                '[[' . $this->fieldIdName . ']] = :entityId',
                [':entityId' => $entityId])
            ->execute();
    }

    /**
     * Populates an auth item with the data fetched from database.
     *
     * @param  array  $row  the data from the auth item table
     *
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className()
            : Role::className();

        if (!isset($row['data'])
            || ($data
                = @unserialize(is_resource($row['data'])
                ? stream_get_contents($row['data']) : $row['data'])) === false
        ) {
            $data = null;
        }

        $attributes = [
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'] ?: null,
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];

        if ($class === Role::class) {
            $class = \common\rbac\roles\Role::class;
            $attributes['priority'] = intval($row['priority'] ?? 0);
        }

        return new $class($attributes);
    }
}
