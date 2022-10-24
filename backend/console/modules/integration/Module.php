<?php

namespace console\modules\integration;

use yii\base\BootstrapInterface;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $id = 'integration';

    public $settings = [];

    public function setSettings($values)
    {
        $this->settings = ArrayHelper::merge($this->settings, $values);
    }

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'console\modules\integration\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        \Yii::setAlias('@integration-cdn', '@root/integration/cdn');
    }

    public function bootstrap($app)
    {
    }
}
