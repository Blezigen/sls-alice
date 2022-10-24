<?php

namespace console;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use yii\console\Application;

abstract class AbstractConsoleController extends \yii\console\Controller
{
    /**
     * @var mixed|ConsoleOutput
     */
    protected $output;

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

    public function runAction($id, $params = [])
    {
        $this->output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, true);
        foreach ($this->colors() as $color => $options) {
            $this->output->getFormatter()->setStyle($color, $options);
        }

        return parent::runAction($id, $params);
    }

    public function drawTable($entityArray, $whiteListColumn = null)
    {
        $table = new Table($this->output);
        $columns = [];
        $rows = [];

        foreach ($entityArray as $values) {
            $row = [];
            foreach ($values as $column => $value) {
                if ($whiteListColumn !== null
                    && !in_array($column, $whiteListColumn)
                ) {
                    continue;
                }

                if (array_search("$column", $columns) === false) {
                    $columns[] = "$column";
                }

                $columnIndex = array_search("$column", $columns);
                $row[$columnIndex] = $value;
            }

            $rows[] = $row;
        }

        $table->setHeaders($columns);
        $table->addRows($rows);
        $table->render();

        return $table;
    }

    public function consoleLog($message, $writeLn = false)
    {
        if (\Yii::$app instanceof Application) {
            $this->output->write($message, $writeLn, OutputInterface::OUTPUT_NORMAL);
        }
    }
}
