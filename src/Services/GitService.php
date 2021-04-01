<?php

namespace Bdelespierre\GitStats\Services;

use Bdelespierre\GitStats\Interfaces\ExecutableFinderInterface;
use Bdelespierre\GitStats\Interfaces\GitServiceInterface;
use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class GitService implements GitServiceInterface
{
    protected ProcessServiceInterface $process;
    protected ExecutableFinderInterface $finder;
    protected ?string $git;

    public function __construct(
        ProcessServiceInterface $process,
        ExecutableFinderInterface $finder
    ) {
        $this->process = $process;
        $this->finder = $finder;
        $this->git = $this->getGitPath();
    }

    private function git(array $command): Process
    {
        $process = $this->process->make([$this->git, ...$command]);
        $process->run();

        return $process;
    }

    private function getGitPath(): ?string
    {
        return $this->finder->find('git');
    }

    public function isGitAvailable(): bool
    {
        return $this->git != null;
    }

    public function isGitRepository(): bool
    {
        return $this->git(["rev-parse", "--git-dir"])->isSuccessful();
    }

    public function updateIndex(): bool
    {
        return $this->git(["update-index", "-q", "--ignore-submodules", "--refresh"])->isSuccessful();
    }

    public function hasUnstagedChanges(): bool
    {
        return ! $this->git(["diff-files", "--quiet", "--ignore-submodules", "--"])->isSuccessful();
    }

    public function hasUncommittedChanges(): bool
    {
        return ! $this->git(["diff-index", "--cached", "--quiet", "HEAD", "--ignore-submodules", "--"])->isSuccessful();
    }

    public function isValidBranch(string $branch): bool
    {
        return $this->git(["rev-parse", "--verify", $branch])->isSuccessful();
    }

    public function checkout(string $commit): bool
    {
        return $this->git(["checkout", $commit])->isSuccessful();
    }

    public function getCommits(string $branch): iterable
    {
        return array_filter(explode(PHP_EOL, $this->git(["rev-list", $branch], true)->getOutput()));
    }

    public function getCommitTimestamp(string $commit): int
    {
        return (int) trim($this->git(["show", "-s", "--format=%ct", $commit])->getOutput());
    }
}
