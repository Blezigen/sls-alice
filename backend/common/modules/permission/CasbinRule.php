<?php

namespace common\modules\permission;

class CasbinRule extends \yii\permission\models\CasbinRule
{
    public function rules()
    {
        return [
            [['ptype', 'v0'], 'required'],
            [['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5', 'v6', 'v7', 'v8', 'v9'], 'safe'],
        ];
    }
}
