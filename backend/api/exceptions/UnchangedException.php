<?php

namespace api\exceptions;

use yii\web\HttpException;

class UnchangedException extends HttpException
{
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct(400, 'Попытка произвести изменение - без изменения.', 400, $previous);
    }
}
