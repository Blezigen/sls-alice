<?php

namespace console\modules\generator;

use OpenApi\Annotations\PathItem;

class Module extends \yii\base\Module
{
    public $id = 'generator/default';
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'console\modules\generator\controllers';
    public $generators = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    public function _docs()
    {
        $paths = [];

        $paths[] = new PathItem([
        ]);

        return $paths;
    }
}
