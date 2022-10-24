<?php

namespace api\helpers;

class UrlRule extends \yii\web\UrlRule
{
    public function getParams()
    {
        return $this->getParamRules();
    }
}
