<?php

namespace common\actions;

class OptionsAction extends \yii\rest\OptionsAction
{
    public $resourceOptions = ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
}
