<?php

namespace common\modules\permission;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\web\User;

class AccessRule extends \yii\filters\AccessRule
{
    public $enforce = false;

    protected $_calc_enforce = [];

    public function allows($action, $user, $request)
    {
        $allowAction = $this->matchAction($action);
        $allowRole = $this->matchRole($user);
        $allowIP = $this->matchIP($request->getUserIP());
        $allowVerb = $this->matchVerb($request->getMethod());
        $allowController = $this->matchController($action->controller);
        $allowCustom = $this->matchCustom($action);
        $allowEnforce = $this->matchEnforce($user);

        $token = 'AccessRule: ' . json_encode([
                'allowAction({' . implode(',', $this->actions ?? []) . '})' => $allowAction,
                'allowRole({' . implode(',', $this->roles ?? []) . '})' => $allowRole,
                'allowIP' => $allowIP,
                'allowVerb' => $allowVerb,
                'allowController' => $allowController,
                'allowCustom' => $allowCustom,
                'allowEnforce({' . implode(',', array_values($this->_calc_enforce)) . '})' => $allowEnforce,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        \Yii::beginProfile($token, __METHOD__);

        $allow = $allowAction
            && $allowRole
            && $allowIP
            && $allowVerb
            && $allowController
            && $allowCustom
            && $allowEnforce;

        \Yii::endProfile($token, __METHOD__);

        if ($allow) {
            return $this->allow ? true : false;
        }

        return null;
    }

    public function matchEnforce(User $user)
    {
        if ($this->enforce === false) {
            return true;
        }

        if (!is_array($this->enforce)) {
            throw new \Exception('enforce not array');
        }

        if (method_exists($user, 'enforce')) {;
            $baseEnforce = [
                'module' => \Yii::$app->controller->module->id,
                'controller' => \Yii::$app->controller->id,
                'action' => \Yii::$app->controller->action->id,
                'act' => '*',
                'model' => '*',
                'attr' => '*',
            ];

            $this->_calc_enforce = ArrayHelper::merge($baseEnforce, $this->enforce);

            return $user->enforce(
                $this->_calc_enforce['module'],
                $this->_calc_enforce['controller'],
                $this->_calc_enforce['action'],
                $this->_calc_enforce['act'],
                $this->_calc_enforce['model'],
                $this->_calc_enforce['attr'],
            );
        }

        return false;
    }

    /**
     * @param  User  $user  the user object
     *
     * @return bool whether the rule applies to the role
     *
     * @throws InvalidConfigException if User component is detached
     */
    protected function matchRole($user)
    {
        \Yii::$app->permission->init();
        $items = empty($this->roles) ? [] : $this->roles;

        if (!empty($this->permissions)) {
            $items = array_merge($items, $this->permissions);
        }

        if (empty($items)) {
            return true;
        }

        if ($user === false) {
            throw new InvalidConfigException('The user application component must be available to specify roles in AccessRule.');
        }

        foreach ($items as $item) {
            if ($item === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($item === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } else {
                if (\Yii::$app->permission->hasRoleForUser("{$user->getId()}",
                    $item)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
