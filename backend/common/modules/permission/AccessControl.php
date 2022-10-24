<?php

namespace common\modules\permission;

use yii\filters\AccessRule;

class AccessControl extends \yii\filters\AccessControl
{
    public $ruleConfig = ['class' => AccessRule::class];

    public function ensureRules()
    {
        $user = $this->user;
        $request = \Yii::$app->getRequest();
        /* @var $rule AccessRule */
        foreach ($this->rules as $rule) {
            $action = \Yii::$app->controller->action;
            if ($allow = $rule->allows($action, $user, $request)) {
                return true;
            } elseif ($allow === false) {
                if (isset($rule->denyCallback)) {
                    call_user_func($rule->denyCallback, $rule, $action);
                } elseif ($this->denyCallback !== null) {
                    call_user_func($this->denyCallback, $rule, $action);
                } else {
                    $this->denyAccess($user);
                }

                return false;
            }
        }
        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, null, $action);
        } else {
            $this->denyAccess($user);
        }

        return false;
    }
}
