<?php

namespace Bdelespierre\GitStats\Services;

use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessService implements ProcessServiceInterface
{
    public function make(array $command): Process
    {
        return new Process($command);
    }

    public function run(array $command): Process
    {
        $process = $this->make($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
