<?php

namespace Tests\Services;

use Bdelespierre\GitStats\Services\ExecutableFinder;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder as SymfonyExecutableFinder;

class ExecutableFinderTest extends TestCase
{
    public function testFind()
    {
        $sfFinder = Mockery::mock(SymfonyExecutableFinder::class . '[find]');
        $sfFinder->expects()->find('ls')->andReturns('/usr/bin/ls');

        $service = new ExecutableFinder($sfFinder);

        $this->assertEquals(
            '/usr/bin/ls',
            $service->find('ls'),
        );
    }
}
