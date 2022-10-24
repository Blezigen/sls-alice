<?php

namespace console\controllers;

use common\helpers\PermissionHelper;

class RbacController extends \yii\console\Controller
{
    // before: yii migrate --migrationPath=@yii/rbac/migrations/

    public function actionInit()
    {
        $auth = \Yii::$app->authManager;

        $listRoles = [
          ['name' => 'user', 'description' => 'Пользователь'],
          ['name' => 'admin', 'description' => 'Администратор'],
        ];
        $authRoles = [];

        foreach ($listRoles as $itemRole) {
            $userRole = $auth->createRole($itemRole['name']);
            $userRole->description = $itemRole['description'];
            $auth->add($userRole);

            $authRoles[$itemRole['name']] = $userRole;
        }

        $rule = new \common\rbac\OwnRule();
        $auth->add($rule);

        $tablePermissions = PermissionHelper::getTablePermissions();

        foreach ($tablePermissions as $tableName => $perms) {
            foreach ($perms as $type => $roles) {
                // добавляем разрешение "$type"
                $modelPerm = $auth->createPermission($type . $tableName);
                $modelPerm->description = "{$type} {$tableName}";
                $auth->add($modelPerm);

                // вешаем разрешения на роли
                foreach ($roles as $role) {
                    $auth->addChild($authRoles[$role], $modelPerm);
                }
            }
        }

        $tableOwnPermissions = PermissionHelper::getTableOwnPermissions();

        foreach ($tableOwnPermissions as $tableName => $perms) {
            foreach ($perms as $type => $roles) {
                // добавляем разрешение "$type"
                $modelOwnPerm = $auth->createPermission($type . 'Own' . $tableName);
                $modelOwnPerm->description = "{$type} own {$tableName}";
                $modelOwnPerm->ruleName = $rule->name;
                $auth->add($modelOwnPerm);

                // $modelOwnPerm будет использоваться из $modelPerm
                $modelPerm = $auth->getPermission('update' . $tableName);
                $auth->addChild($modelOwnPerm, $modelPerm);

                // вешаем разрешения на роли
                foreach ($roles as $role) {
                    $auth->addChild($authRoles[$role], $modelOwnPerm);
                }
            }
        }
    }
}

/*
https://www.yiiframework.com/doc/guide/2.0/ru/security-authorization

Назначение роли
$auth = Yii::$app->authManager;
$authorRole = $auth->getRole('author');
$auth->assign($authorRole, $user->getId());
*/
