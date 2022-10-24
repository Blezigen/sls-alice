<?php

namespace common\modules\webhook_module;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->setModule('webhooks', [
            'id' => 'webhooks',
            'class' => \common\modules\webhook_module\Module::class,
        ]);
    }
}
