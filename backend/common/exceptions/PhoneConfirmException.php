<?php

namespace common\exceptions;

use yii\web\HttpException;

class PhoneConfirmException extends HttpException
{
    public $timeout = false;
    public $isRequiredCodeHeader = false;
    public $phoneAttempt = false;
    public $phoneAttemptLeft = false;

    public static function phoneCodeRequired($phoneAttempt = false, $phoneAttemptLeft = false)
    {
        $static = new static(400, 'Необходимо подтвердить номер телефона');

        $static->isRequiredCodeHeader = true;

        return $static;
    }

    public static function tooManyAttempt($phoneAttempt = false, $phoneAttemptLeft = false)
    {
        $static = new static(429, 'Слишком много попыток отправки кода подтверждения');
        $static->phoneAttempt = $phoneAttempt;
        $static->phoneAttemptLeft = $phoneAttemptLeft;

        return $static;
    }

    public static function inTimeout($timeout = 60, $phoneAttempt = false, $phoneAttemptLeft = false)
    {
        $static = new static(429, 'Повторите попытку позднее');
        $static->timeout = $timeout;
        $static->phoneAttempt = $phoneAttempt;
        $static->phoneAttemptLeft = $phoneAttemptLeft;

        return $static;
    }

    public static function providerNotFound($type)
    {
        return new static(404, "Провайдер \"$type\" для отправки кода подтверждения не найден.");
    }
}
