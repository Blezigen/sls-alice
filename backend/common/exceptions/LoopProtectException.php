<?php

namespace common\exceptions;

class LoopProtectException extends ValidationException
{
    public static function makeSelfSignException($attribute)
    {
        return new self([$attribute => ['Нельзя установить свой ИД как родителя для самого себя.']]);
    }

    public static function makeLoopDetectException($attribute)
    {
        return new self([$attribute => ['Выберите другой корневой элемент, с текущим значением найдена петля']]);
    }
}
