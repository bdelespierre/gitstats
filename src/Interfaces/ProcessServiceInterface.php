<?php

namespace Bdelespierre\GitStats\Interfaces;

use Symfony\Component\Process\Process;

interface ProcessServiceInterface
{
    public function make(array $command): Process;

    public function run(array $command): Process;
}
