<?php

namespace integration\models;

use yii\db\ActiveRecord;

class Integration extends ActiveRecord
{
    // public static function getDb()
    // {
    //     return \Yii::$app->db2;
    // }

    public static function tableName()
    {
        return '{{integration}}';
    }

    public function rules()
    {
        return [
            [[
                'internal_cid',
                'external_cid',
                'service',
                'type',
            ], 'safe']
        ];
    }

    public static function findOrCreate($id = null, $externalId = null, $type = null, $service = null)
    {
        if (!$id || !$externalId || !$type || !$service) {
            return null;
        }

        $data = [
            "internal_cid" => $id,
            "external_cid" => $externalId,
            "service" => $service,
            "type" => $type
        ];

        $model = self::findOne($data);

        if (!$model) {
            $model = new self;
            $model->attributes = $data;
            $model->save();
        }

        return $model;
    }
}
