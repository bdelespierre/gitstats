<?php

namespace Tests\Services;

use Bdelespierre\GitStats\Services\ProcessService;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMake()
    {
        $service = new ProcessService();

        $this->assertInstanceof(
            Process::class,
            $service->make(['ls']),
        );
    }

    /**
     * @depends  testMake
     */
    public function testRun()
    {
        $command = ['ls'];

        // mock a sucessful process
        $process = Mockery::mock(Process::class . '[isSuccessful]', [$command]);
        $process->expects()->isSuccessful()->andReturns(true);

        // mock only ProcessService::make to return the mocked process
        $service = Mockery::mock(ProcessService::class . '[make]');
        $service->expects()->make($command)->andReturns($process);

        $this->assertInstanceof(
            Process::class,
            $service->run($command),
        );
    }

    /**
     * @depends  testMake
     */
    public function testRunFails()
    {
        $this->expectException(ProcessFailedException::class);

        $command = ['ls'];

        // mock an uncessful process
        $process = Mockery::mock(Process::class . '[isSuccessful]', [$command]);
        $process->expects()->isSuccessful()->twice()->andReturns(false);

        // mock only ProcessService::make to return the mocked process
        $service = Mockery::mock(ProcessService::class . '[make]');
        $service->expects()->make($command)->andReturns($process);

        $service->run($command);
    }
}
