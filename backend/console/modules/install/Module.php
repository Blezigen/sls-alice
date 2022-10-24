<?php

namespace console\modules\install;

use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $id = 'install';

    public $settings = [];

    public function setSettings($values)
    {
        $this->settings = ArrayHelper::merge($this->settings, $values);
    }

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'console\modules\install\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::setAlias('@install', '@root/console/modules/install');
        \Yii::setAlias('@install_resources', '@install/resources');
    }

    public function bootstrap($app)
    {
    }
}
