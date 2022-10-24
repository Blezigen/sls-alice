<?php

namespace api\modules\users;

/**
 * collection module definition class
 */
class Module extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\users\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function routes($moduleID)
    {
        $moduleID = $this->id;

        return [
            // <editor-fold desc="collections">
                'POST v1.0/user/devices/query' => "{$moduleID}/device/query",
                'POST v1.0/user/devices/action' => "{$moduleID}/device/action",
                'GET v1.0/user/devices' => "{$moduleID}/device/index",
                'v1.0/user/devices/action' => 'collection/collection/options',
                'v1.0/user/devices/query' => 'collection/collection/options',
                'v1.0/user/devices' => 'collection/collection/options',
            // </editor-fold>
        ];
    }
}
