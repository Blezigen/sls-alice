<?php

namespace api\modules\auth\models\write;

use common\exceptions\NotImplementException;

/**
 * @property string $accept_token
 */
class RegistrationWrite extends \api\modules\registration\models\write\RegistrationWrite
{
    public function fields()
    {
        return array_merge([
            'accept_token' => function () {
                return $this->accept_token;
            },
        ], parent::fields());
    }

    public function getAccept_token()
    {
        throw new NotImplementException();
    }

    public function setAccept_token($value)
    {
        throw new NotImplementException();
    }
}
