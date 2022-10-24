<?php

namespace common\modules\webhook_module\models;

class WebhookQuery extends \yii\db\ActiveQuery
{
    public function all($db = null)
    {
        return parent::all($db);
    }
}
