<?php

namespace common\modules\acceptance\database;

use common\modules\acceptance\queries\AcceptanceQuery;

/**
 * This is the model class for table "{{%acceptances}}".
 *
 * @property int $id
 * @property string|null $accept_token
 * @property int $channel
 * @property string $phone
 * @property string $code
 * @property int $generate_attempt_count
 * @property int $code_attempt_count
 * @property string|null $used_at
 * @property string|null $retry_at
 * @property string|null $expired_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Acceptance extends \common\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%acceptances}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['channel', 'phone', 'generate_attempt_count', 'code_attempt_count'], 'required'],
            [['channel', 'generate_attempt_count', 'code_attempt_count'], 'default', 'value' => null],
            [['generate_attempt_count', 'code_attempt_count'], 'integer'],
            [['used_at', 'retry_at', 'expired_at', 'created_at', 'updated_at'], 'safe'],
            [['channel', 'accept_token', 'phone'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 8],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'accept_token' => 'Accept Token',
            'channel' => 'Channel',
            'phone' => 'Phone',
            'code' => 'Code',
            'attempt_count' => 'Attempt Count',
            'attempt_max' => 'Attempt Max',
            'used_at' => 'Used At',
            'retry_at' => 'Retry At',
            'expired_at' => 'Expired At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return AcceptanceQuery the active query used by this AR class
     */
    public static function find()
    {
        return new AcceptanceQuery(get_called_class());
    }
}
