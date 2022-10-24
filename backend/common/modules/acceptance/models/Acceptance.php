<?php

namespace common\modules\acceptance\models;

/**
 * This is the model class for table "{{%acceptances}}".
 */
class Acceptance extends \common\modules\acceptance\database\Acceptance
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), []);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'id' => 'ID',
            // 'accept_token' => 'Accept Token',
            // 'channel' => 'Channel',
            // 'phone' => 'Phone',
            // 'code' => 'Code',
            // 'attempt_count' => 'Attempt Count',
            // 'attempt_max' => 'Attempt Max',
            // 'used_at' => 'Used At',
            // 'retry_at' => 'Retry At',
            // 'expired_at' => 'Expired At',
            // 'created_at' => 'Created At',
            // 'updated_at' => 'Updated At',
        ]);
    }
}
