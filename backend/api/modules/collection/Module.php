<?php

namespace api\modules\collection;

/**
 * collection module definition class
 */
class Module extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\modules\collection\controllers';

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
            "PUT,PATCH collections/<id:\d+>" => "{$moduleID}/collection/update",
            "DELETE collections/<id:\d+>" => "{$moduleID}/collection/delete",
            "GET,HEAD collections/<id:\d+>" => "{$moduleID}/collection/view",
            'POST collections' => "{$moduleID}/collection/create",
            'GET,HEAD collections' => "{$moduleID}/collection/index",
            "OPTIONS collections/<id:\d+>" => "{$moduleID}/collection/options",
            'OPTIONS collections' => "{$moduleID}/collection/options",
            // </editor-fold>
        ];
    }
}
