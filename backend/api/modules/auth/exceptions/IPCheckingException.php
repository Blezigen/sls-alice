<?php

namespace api\modules\auth\exceptions;

use yii\web\HttpException;

class IPCheckingException extends HttpException
{
    public function __construct(
        $message = null
    ) {
        parent::__construct(400, $message, $code = 0, $previous = null);
    }
}
