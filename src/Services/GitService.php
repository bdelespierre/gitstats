<?php

namespace Bdelespierre\GitStats\Services;

use Bdelespierre\GitStats\Interfaces\GitServiceInterface;
use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class GitService implements GitServiceInterface
{
    protected ProcessServiceInterface $process;

    protected string $git;

    public function __construct(ProcessServiceInterface $process)
    {
        $this->process = $process;
        $this->git = $this->getGitPath();
    }

    private function git(array $command, bool $mustRun = false): Process
    {
        $process = $this->process->make([$this->git, ...$command]);
        $process->{$mustRun ? 'mustRun' : 'run'}();

        return $process;
    }

    private function getGitPath(): string
    {
        return (new ExecutableFinder())->find('git');
    }

    public function isGitAvailable(): bool
    {
        return $this->git != null;
    }

    public function isGitRepository(): bool
    {
        return $this->git(["rev-parse", "--git-dir"])->isSuccessful();
    }

    /**
     * @see  https://www.spinics.net/lists/git/msg142043.html
     */
    public function isWorkTreeClean(?string &$reason = null): bool
    {
        // update the index
        $this->git(["update-index", "-q", "--ignore-submodules", "--refresh"], true);

        // unstaged changes in the working tree?
        if (! $this->git(["diff-files", "--quiet", "--ignore-submodules", "--"])->isSuccessful()) {
            $reason = "you have unstaged changes";
            return false;
        }

        // uncommitted changes in the index?
        if (! $this->git(["diff-index", "--cached", "--quiet", "HEAD", "--ignore-submodules", "--"])->isSuccessful()) {
            $reason = "your index contains uncommitted changes";
            return false;
        }

        return true;
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
        $commits = $this->git(["rev-list", $branch], true)->getOutput();
        $commits = explode(PHP_EOL, $commits);
        $commits = array_filter($commits);

        return $commits;
    }

    public function getCommitTimestamp(string $commit): int
    {
        return (int) trim($this->git(["show", "-s", "--format=%ct", $commit])->getOutput());
    }
}
