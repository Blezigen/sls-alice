<?php

namespace common\modules\permission;

use common\models\Account;
use Redbox\PersonalSettings\ActiveOption;
use Redbox\PersonalSettings\exceptions\ValidationException;
use Redbox\PersonalSettings\SettingAccess;
use Redbox\PersonalSettings\SettingSummary;
use yii\helpers\ArrayHelper;
use yii\web\Application;

class User extends \yii\web\User
{
    public function getEnforceName()
    {
        $userId = 'guest';

        if (\Yii::$app instanceof \yii\console\Application) {
            return 'console';
        }

//        dd(ArrayHelper::toArray($this));
        if (!$this->isGuest) {
            $userId = "{$this->getId()}";
        }

        return $userId;
    }

    public function enforceYii(
        $act = '*',
        $model = '*',
        $attr = '*'
    ) {
        return $this->enforce(
            \Yii::$app->controller->module->id,
            \Yii::$app->controller->id,
            \Yii::$app->controller->action->id,
            $act,
            $model,
            $attr
        );
    }

    public function enforce(
        $module = '*',
        $controller = '*',
        $action = '*',
        $act = '*',
        $model = '*',
        $attr = '*'
    ) {
        $userId = $this->getEnforceName();
        if ($userId === "console") {
            return true;
        }

//        throw new \Exception($userId);

        $token = \Yii::t('app',
            "enforce('{userId}','{module}','{controller}','{action}','{act}','{model}','{attr}')",
            [
                'userId'     => $userId,
                'module'     => $module,
                'controller' => $controller,
                'action'     => $action,
                'act'        => $act,
                'model'      => $model,
                'attr'       => $attr,
            ]);

        \Yii::beginProfile($token, __METHOD__);
        $result = \Yii::$app->permission->enforce(
            $userId, $module, $controller, $action, $act, $model, $attr
        );
        \Yii::endProfile($token, __METHOD__);

        return $result;
    }

    /**
     * @throws ValidationException
     */
    public function get($section, $key, $default = null)
    {
        $identities = [];
        $roles = [];

        if (\Yii::$app instanceof Application && !$this->isGuest) {
            /** @var Account $identity */
            $identity = $this->identity;
            $contractor = $identity->contractor;
            if ($contractor) {
                $identities[] = $contractor->id;
            }
            $roles[] = $identity->role;
        } else {
            $roles[] = 'guest';
        }

        $summary = new SettingSummary(
            access: SettingAccess::make([
                'identities' => $identities,
                'roles'      => $roles,
            ]),
        );

        return \Yii::$app->settings->getValue(
            $section,
            $key,
            $default,
            $summary
        );
    }

    public function set(
        $section,
        $key,
        $value,
        ActiveOption $activeOption = null
    ) {
        if ($this->isGuest) {
            throw new \Exception();
        }

        \Yii::$app->settings->setValue($section, $key, $value,
            new SettingSummary(
                access: SettingAccess::make([
                    'identities' => [$this->id],
                ]),
            ), $activeOption);
    }
}
