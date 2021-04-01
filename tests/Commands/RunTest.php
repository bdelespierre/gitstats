<?php

namespace Tests\Commands;

use Bdelespierre\GitStats\Commands\Run;
use Bdelespierre\GitStats\Interfaces\GitServiceInterface;
use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Tests\Helpers\DummyOutput;

class RunTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @dataProvider  runDataProvider
     */
    public function testRun($git, $inputs, $result)
    {
        $input = $this->getInput($inputs);
        $output = new DummyOutput();

        $command = $this->getRunCommand(
            $this->getGitServiceMock($git),
            $this->getProcessServiceMock()
        );

        $this->assertEquals(
            $result['exitCode'],
            $command->run($input, $output)
        );

        $this->assertStringMatchesFormat(
            $result['output'],
            $output->buffer
        );
    }

    public function runDataProvider()
    {
        return [
            "Successful run" => [
                'git' => [
                    'branch' => "master",
                    'commits' => [
                        "0e75bcac756226986f9e6ba745c0f1944ee482db",
                        "1cd263613b1b3bb96bff86a04c0e0c42c9427f32",
                        "2159438bd963174acac8518d9d58e85fc5fb431f",
                        "3dd0cf355552553eebc3614ada24c305393c628c",
                        "4731d6c9d91c8e4f07db0bec6e22c912a55baef2",
                    ],
                    'timestamps' => [
                        1617273600,
                        1617273601,
                        1617273602,
                        1617273603,
                        1617273604,
                    ],
                ],
                'inputs' => [
                    'branch' => "master",
                    'tasks' => __DIR__ . '/data/tasks_a.php',
                ],
                'result' => [
                    'exitCode' => Command::SUCCESS,
                    'output' => file_get_contents(__DIR__ . '/data/result.csv')
                ],
            ],

            "Successful run with string commands" => [
                'git' => [
                    'branch' => "master",
                    'commits' => [
                        "0e75bcac756226986f9e6ba745c0f1944ee482db",
                        "1cd263613b1b3bb96bff86a04c0e0c42c9427f32",
                        "2159438bd963174acac8518d9d58e85fc5fb431f",
                        "3dd0cf355552553eebc3614ada24c305393c628c",
                        "4731d6c9d91c8e4f07db0bec6e22c912a55baef2",
                    ],
                    'timestamps' => [
                        1617273600,
                        1617273601,
                        1617273602,
                        1617273603,
                        1617273604,
                    ],
                ],
                'inputs' => [
                    'branch' => "master",
                    'tasks' => __DIR__ . '/data/tasks_b.php',
                ],
                'result' => [
                    'exitCode' => Command::SUCCESS,
                    'output' => file_get_contents(__DIR__ . '/data/result.csv')
                ],
            ],

            "Git isn't available" => [
                'git' => ['doubles' => ['isGitAvailable' => false]],
                'inputs' => [],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Cannot proceed: unable to find git command.</error>\n",
                ],
            ],

            "Not a Git repository" => [
                'git' => ['doubles' => ['isGitRepository' => false]],
                'inputs' => [],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Cannot proceed: %s is not a git repository.</error>\n",
                ],
            ],

            "Unable to update index" => [
                'git' => ['doubles' => ['updateIndex' => false]],
                'inputs' => [],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Cannot proceed: unable to update index.</error>\n",
                ],
            ],

            "You have unstaged changes" => [
                'git' => ['doubles' => ['hasUnstagedChanges' => true]],
                'inputs' => [],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Cannot proceed: you have unstaged changes. " .
                        "Please commit or stash them.</error>\n",
                ],
            ],

            "Your index contains uncommitted changes" => [
                'git' => ['doubles' => ['hasUncommittedChanges' => true]],
                'inputs' => [],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Cannot proceed: your index contains uncommitted changes. " .
                        "Please commit or stash them.</error>\n",
                ],
            ],

            "Invalid branch" => [
                'git' => [
                    'branch' => "master",
                    'doubles' => ['isValidBranch' => false]
                ],
                'inputs' => [
                    'branch' => "master",
                ],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>Branch master is not valid.</error>\n",
                ],
            ],

            "Taskfile unreadable" => [
                'git' => [
                    'branch' => "master",
                ],
                'inputs' => [
                    'branch' => "master",
                    'tasks' => "no-such-file",
                ],
                'result' => [
                    'exitCode' => Command::FAILURE,
                    'output' => "<error>File no-such-file does not exists.</error>\n",
                ],
            ],
        ];
    }

    private function getRunCommand($git, $process): Run
    {
        return new class ($git, $process) extends Run
        {
            public function run(InputInterface $input, OutputInterface $output)
            {
                // dismiss all the SF stuff we don't need to test
                // and just invoke execute.
                return $this->execute($input, $output);
            }
        };
    }

    private function getGitServiceMock(array $setup): GitServiceInterface
    {
        $setup += [
            'commits' => [],
            'timestamps' => [],
            'doubles' => [],
        ];

        $setup['doubles'] += [
            'checkout' => true,
            'isGitAvailable' => true,
            'isGitRepository' => true,
            'updateIndex' => true,
            'hasUnstagedChanges' => false,
            'hasUncommittedChanges' => false,
            'isValidBranch' => true,
        ];

        $service = Mockery::mock(GitServiceInterface::class);

        $service->shouldReceive(
            $setup['doubles'] + ['getCommits' => $setup['commits']]
        );

        $service->shouldReceive('checkout')
            ->withArgs(fn($arg) => in_array($arg, $setup['commits']))
            ->andReturns($setup['doubles']['checkout']);

        $service->shouldReceive('getCommitTimestamp')
            ->withArgs(fn($arg) => in_array($arg, $setup['commits']))
            ->andReturnValues($setup['timestamps']);

        return $service;
    }

    private function getProcessServiceMock(): ProcessServiceInterface
    {
        $service = Mockery::mock(ProcessServiceInterface::class);

        $service->shouldReceive('exec')
            ->withArgs(fn($arg) => (bool) preg_match('/cmd_[abc]/', $arg))
            ->andReturnUsing(fn($arg) => "{$arg}_output");

        $service->shouldReceive('run')
            ->withArgs(fn($arg) => (bool) preg_match('/cmd_[abc]/', $arg[0]))
            ->andReturnUsing(function ($arg) {
                $process = Mockery::mock(Process::class . '[getOutput]', [$arg]);
                $process->expects()->getOutput()->andReturns($arg[0] . '_output');

                return $process;
            });

        return $service;
    }

    private function getInput(array $setup): InputInterface
    {
        $input = Mockery::mock(InputInterface::class);

        foreach ($setup as $key => $value) {
            $input->expects()->getArgument($key)->andReturns($value);
        }

        return $input;
    }
}
