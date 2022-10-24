<?php

namespace common\modules\permission;

class Module extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\filter\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }

    public function routes($moduleID)
    {
        return [];
    }
}
