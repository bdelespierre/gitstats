<?php

namespace Bdelespierre\GitStats\Interfaces;

interface ExecutableFinderInterface
{
    public function find(string $command): ?string;
}
