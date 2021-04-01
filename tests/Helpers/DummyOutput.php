<?php

namespace Tests\Helpers;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DummyOutput implements OutputInterface
{
    public string $buffer = "";

    public function write($messages, bool $newline = false, int $options = 0)
    {
        if (! is_iterable($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->buffer .= $message;

            if ($newline) {
                $this->buffer .= PHP_EOL;
            }
        }
    }

    public function writeln($messages, int $options = 0)
    {
        return $this->write($messages, true, $options);
    }

    public function setVerbosity(int $level)
    {
        //
    }

    public function getVerbosity()
    {
        //
    }

    public function isQuiet()
    {
        //
    }

    public function isVerbose()
    {
        //
    }

    public function isVeryVerbose()
    {
        //
    }

    public function isDebug()
    {
        //
    }

    public function setDecorated(bool $decorated)
    {
        //
    }

    public function isDecorated()
    {
        //
    }

    public function setFormatter(OutputFormatterInterface $formatter)
    {
        //
    }

    public function getFormatter()
    {
        //
    }
}
