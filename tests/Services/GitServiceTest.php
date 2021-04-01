<?php

namespace Tests\Services;

use Bdelespierre\GitStats\Interfaces\ExecutableFinderInterface;
use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Bdelespierre\GitStats\Services\GitService;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class GitServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function mockFinderService(?string $path = '/usr/bin/git'): ExecutableFinderInterface
    {
        $service = Mockery::mock(ExecutableFinderInterface::class);
        $service->expects()->find('git')->andReturns($path);

        return $service;
    }

    private function mockProcessService(
        ?array $command = null,
        ?bool $isSuccessful = null,
        ?string $output = null
    ): ProcessServiceInterface {
        $service = Mockery::mock(ProcessServiceInterface::class);

        if (! is_null($command)) {
            $process = Mockery::mock(Process::class);
            $process->shouldReceive('run')->once();
            $service->expects()->make($command)->andReturns($process);
        }

        if (isset($process) && ! is_null($isSuccessful)) {
            $process->expects()->isSuccessful()->andReturns($isSuccessful);
        }

        if (isset($process) && ! is_null($output)) {
            $process->expects()->getOutput()->andReturns($output);
        }

        return $service;
    }

    /**
     * @dataProvider  isGitAvailableDataProvider
     */
    public function testIsGitAvailable($path, $result)
    {
        $git = new GitService(
            $this->mockProcessService(),
            $this->mockFinderService($path)
        );

        $this->assertEquals(
            $result,
            $git->isGitAvailable()
        );
    }

    public function isGitAvailableDataProvider()
    {
        return [
            "Git is available" => [
                'path' => "/usr/bin/git",
                'result' => true,
            ],

            "Git isn't available" => [
                'path' => null,
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider  isGitRepositoryDataProvider
     */
    public function testIsGitRepository($isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "rev-parse", "--git-dir"],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->isGitRepository()
        );
    }

    public function isGitRepositoryDataProvider()
    {
        return [
            "Is a Git repository" => [
                'isSuccessful' => true,
                'result' => true,
            ],

            "Isn't a Git repository" => [
                'isSuccessful' => false,
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider  updateIndexDataProvider
     */
    public function testUpdateIndex($isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "update-index", "-q", "--ignore-submodules", "--refresh"],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->updateIndex()
        );
    }

    public function updateIndexDataProvider()
    {
        return [
            'Update index successful' => [
                'isSuccessful' => true,
                'result' => true,
            ],

            'Update index failure' => [
                'isSuccessful' => false,
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider  hasUnstagedChangesDataProvider
     */
    public function testHasUnstagedChanges($isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "diff-files", "--quiet", "--ignore-submodules", "--"],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->hasUnstagedChanges()
        );
    }

    public function hasUnstagedChangesDataProvider()
    {
        return [
            "Doesn't have unstaged changes" => [
                'isSuccessful' => true,
                'result' => false,
            ],

            "Has unstaged changes" => [
                'isSuccessful' => false,
                'result' => true,
            ],
        ];
    }

    /**
     * @dataProvider  hasUncommittedChangesDataProvider
     */
    public function testHasUncommittedChanges($isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "diff-index", "--cached", "--quiet", "HEAD", "--ignore-submodules", "--"],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->hasUncommittedChanges()
        );
    }

    public function hasUncommittedChangesDataProvider()
    {
        return [
            "Doesn't have uncommitted changes" => [
                'isSuccessful' => true,
                'result' => false,
            ],

            "Has uncommitted changes" => [
                'isSuccessful' => false,
                'result' => true,
            ],
        ];
    }

    /**
     * @dataProvider  isValidBranchDataProvider
     */
    public function testIsValidBranch($branch, $isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "rev-parse", "--verify", $branch],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->isValidBranch($branch)
        );
    }

    public function isValidBranchDataProvider()
    {
        return [
            "Branch exists" => [
                'branch' => "foobar",
                'isSuccessful' => true,
                'result' => true,
            ],

            "Branch doesn't exists" => [
                'branch' => "foobar",
                'isSuccessful' => false,
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider  checkoutDataProvider
     */
    public function testCheckout($commit, $isSuccessful, $result)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "checkout", $commit],
                $isSuccessful
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $result,
            $git->checkout($commit)
        );
    }

    public function checkoutDataProvider()
    {
        return [
            "Branch exists" => [
                'commit' => "0e75bcac756226986f9e6ba745c0f1944ee482db",
                'isSuccessful' => true,
                'result' => true,
            ],

            "Branch doesn't exists" => [
                'commit' => "0e75bcac756226986f9e6ba745c0f1944ee482db",
                'isSuccessful' => false,
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider  getCommitsDataProvider
     */
    public function testGetCommits($branch, $commits)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "rev-list", $branch],
                null,
                implode(PHP_EOL, $commits),
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $commits,
            $git->getCommits($branch)
        );
    }

    public function getCommitsDataProvider()
    {
        return [
            'Branch with commits' => [
                'branch' => "foobar",
                'commits' => [
                    "0e75bcac756226986f9e6ba745c0f1944ee482db",
                    "1cd263613b1b3bb96bff86a04c0e0c42c9427f32",
                    "3159438bd963174acac8518d9d58e85fc5fb431f",
                    "2dd0cf355552553eebc3614ada24c305393c628c",
                    "a731d6c9d91c8e4f07db0bec6e22c912a55baef2",
                ],
            ],

            'Branch without commits' => [
                'branch' => "foobar",
                'commits' => [],
            ],
        ];
    }

    /**
     * @dataProvider  getCommitTimestampDataProvider
     */
    public function testGetCommitTimestamp($commit, $timestamp)
    {
        $git = new GitService(
            $this->mockProcessService(
                ["/usr/bin/git", "show", "-s", "--format=%ct", $commit],
                null,
                (string) $timestamp,
            ),
            $this->mockFinderService()
        );

        $this->assertEquals(
            $timestamp,
            $git->getCommitTimestamp($commit)
        );
    }

    public function getCommitTimestampDataProvider()
    {
        return [
            'A commit with a timestamp' => [
                'commit' => "0e75bcac756226986f9e6ba745c0f1944ee482db",
                'timestamp' => 1617273604,
            ],
        ];
    }
}
