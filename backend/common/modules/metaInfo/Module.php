<?php

namespace common\modules\metaInfo;

/**
 * metaInfo module definition class
 */
class Module extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'common\modules\metaInfo\controllers';

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
        $moduleID = $this->id;

        return [
            // <editor-fold desc="_meta_common_fields">
            "PUT,PATCH _meta_common_fields/<id:\d>" => 'metaInfo/metaCommonField/update',
            "DELETE _meta_common_fields/<id:\d>" => 'metaInfo/metaCommonField/delete',
            "GET,HEAD _meta_common_fields/<id:\d>" => 'metaInfo/metaCommonField/view',
            'POST _meta_common_fields' => 'metaInfo/metaCommonField/create',
            'GET,HEAD _meta_common_fields' => 'metaInfo/metaCommonField/index',
            "_meta_common_fields/<id:\d>" => 'metaInfo/metaCommonField/options',
            '_meta_common_fields' => 'metaInfo/metaCommonField/options',
            // </editor-fold>
        ];
    }
}
