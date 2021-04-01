<?php

namespace Bdelespierre\GitStats\Services;

use Bdelespierre\GitStats\Interfaces\ExecutableFinderInterface;
use Symfony\Component\Process\ExecutableFinder as SymfonyExecutableFinder;

class ExecutableFinder implements ExecutableFinderInterface
{
    protected $symfonyFinder;

    public function __construct(SymfonyExecutableFinder $finder)
    {
        $this->symfonyFinder = $finder;
    }

    public function find(string $command): ?string
    {
        return $this->symfonyFinder->find($command);
    }
}
