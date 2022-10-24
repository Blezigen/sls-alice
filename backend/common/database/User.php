<?php

namespace common\database;

use Yii;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property int $id
 * @property int $status_id
 * @property string $username
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $auth_key
 * @property string|null $last_login_at
 * @property string $created_at
 * @property string|null $deleted_at
 * @property string|null $updated_at
 *
 */
class User extends \common\BaseActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
            [['status_id'], 'default', 'value' => null],
            [['status_id'], 'integer'],
            "RequiredUsername" => [['username'], 'required'],
            "RequiredPasswordHash" => [['password_hash'], 'required'],
            "RequiredAuthKey" => [['auth_key'], 'required'],
            "RequiredCreatedAt" => [['created_at'], 'required'],
            [['last_login_at', 'created_at', 'deleted_at', 'updated_at'], 'safe'],
            [['username', 'password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['password_reset_token'], 'unique'],
            [['username'], 'unique'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status_id' => 'Status ID',
            'username' => 'Username',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'auth_key' => 'Auth Key',
            'last_login_at' => 'Last Login At',
            'created_at' => 'Created At',
            'deleted_at' => 'Deleted At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * {@inheritdoc}
     * @return \common\queries\UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\queries\UserQuery(get_called_class());
    }
}
