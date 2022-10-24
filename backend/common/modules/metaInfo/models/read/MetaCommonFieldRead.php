<?php

namespace common\modules\metaInfo\models\read;

class MetaCommonFieldRead extends \common\models\MetaCommonField
{
    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return array_merge(parent::extraFields(), []);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'id' => 'ID',
            // 'attribute' => 'Attribute',
            // 'title' => 'Title',
            // 'type' => 'Type',
            // 'rule' => 'Rule',
            // 'payload' => 'Payload',
            // 'created_at' => 'Created At',
            // 'updated_at' => 'Updated At',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaPartnersFieldValues()
    {
        return parent::getMetaPartnersFieldValues();
    }
}
