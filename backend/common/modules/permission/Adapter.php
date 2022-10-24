<?php

namespace common\modules\permission;

class Adapter extends \yii\permission\Adapter
{
    public function __construct(CasbinRule $casbinRule)
    {
        parent::__construct($casbinRule);
    }
}
