<?php

namespace console\exceptions;

use common\exceptions\ValidationException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleHandler extends Handler
{
    private ConsoleOutput $output;

    /**
     * Установка цветов в консоли. Где ключ элемента массива это тег, а значение это цвет.
     *
     * @return OutputFormatterStyle[]
     */
    protected function colors()
    {
        return [
            'err' => new OutputFormatterStyle('red', null, ['blink']),
            'value' => new OutputFormatterStyle('green', null, ['blink']),
            'title' => new OutputFormatterStyle('green', null, ['blink']),
            'green' => new OutputFormatterStyle('green', null, ['blink']),
            'red' => new OutputFormatterStyle('red', null, ['blink']),
            'number' => new OutputFormatterStyle('blue', null, ['blink']),
        ];
    }

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->output = new ConsoleOutput();
        foreach ($this->colors() as $color => $options) {
            $this->output->getFormatter()->setStyle($color, $options);
        }
    }

    protected function renderException($exception)
    {
        if ($exception instanceof ValidationException) {
            foreach ($exception->errors as $item => $value) {
                $this->output->write("<err>{$exception->getMessage()}</err>", true);
                $this->output->write("<err>$item:" . json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT) . '</err>', true);
                $this->output->write('', true);
            }
            return;
        }

        if ($exception instanceof \Redbox\PersonalSettings\exceptions\ValidationException) {
            throw $exception;
            dd($exception->errors);
        }
        parent::renderException($exception);
    }
}
