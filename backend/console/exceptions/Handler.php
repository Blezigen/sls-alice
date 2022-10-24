<?php

namespace console\exceptions;

use common\exceptions\ValidationException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use yii\console\ErrorHandler;
use yii\console\ExitCode;

abstract class Handler extends ErrorHandler
{
    public $maxTraceSourceLines = 1;
}
