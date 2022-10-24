<?php

namespace api;

class Request extends \yii\web\Request
{
    public ?string $version = '1.0';
}
