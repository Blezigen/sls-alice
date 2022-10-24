<?php

namespace api\modules\collection\models\read;

class CollectionRead extends \common\models\Collection
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
            // 'collection' => 'Collection',
            // 'slug' => 'Slug',
            // 'title' => 'Title',
            // 'created_at' => 'Created At',
            // 'updated_at' => 'Updated At',
        ]);
    }

    public static function __makeDocumentationEntity()
    {
        $temp = random_int(1, 600);

        return new self([
            'id' => random_int(1, 10000),
            'collection' => 'collection_temp',
            'slug' => 'slug_temp_' . $temp,
            'title' => 'Временная #' . $temp,
            'created_at' => date('Y-01-d H:i:s'),
            'updated_at' => date('Y-02-d H:i:s'),
        ]);
    }
}
