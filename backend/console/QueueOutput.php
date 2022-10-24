<?php

namespace console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class QueueOutput extends BufferedOutput
{
    private $buffer = '';

    /**
     * Empties buffer and returns its content.
     *
     * @return string
     */
    public function fetch()
    {
        $content = $this->buffer;
        $this->buffer = '';

        return $content;
    }

    public function __construct(
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = true,
        OutputFormatterInterface $formatter = null
    ) {
        parent::__construct($verbosity, $decorated, $formatter);

        $this->getFormatter()->setStyle('error', new QueueOutputFormatterStyle('red', null, []));
        $this->getFormatter()->setStyle('success', new QueueOutputFormatterStyle('green', null, []));
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite(string $message, bool $newline)
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= \PHP_EOL;
        }

        echo $this->fetch();
    }
}
