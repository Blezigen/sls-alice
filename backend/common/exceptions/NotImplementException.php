<?php

namespace common\exceptions;

class NotImplementException extends \Exception
{
    public function __construct(
        $message = 'Метод не реализован',
        $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
