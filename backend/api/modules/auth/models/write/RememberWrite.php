<?php

namespace api\modules\auth\models\write;

use common\BaseModel;
use common\exceptions\NotImplementException;

/**
 * @property string $accept_token
 */
class RememberWrite extends BaseModel
{
    public static function __docAttributeExample()
    {
        return array_merge(parent::__docAttributeExample(), [
            [
                '_type' => 'regex',
                'key' => '/password/',
                'value' => 'NEW_PASSWORD',
            ],
            [
                '_type' => 'regex',
                'key' => '/accept_token/',
                'value' => '{{%generated_accept_token%}}',
            ],
        ]);
    }

    public function getAccept_token()
    {
        return $this->accept_token;
    }

    public function setAccept_token($value)
    {
        $this->accept_token = $value;
    }

    public function fields()
    {
        return array_merge([
            'accept_token' => function () {
                return $this->accept_token;
            },
            'password' => function () {
                throw new NotImplementException();
            },
        ], parent::fields());
    }
}
