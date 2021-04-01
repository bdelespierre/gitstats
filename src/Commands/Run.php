<?php

namespace Bdelespierre\GitStats\Commands;

use Bdelespierre\GitStats\Interfaces\GitServiceInterface;
use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;
use Bdelespierre\GitStats\Services\ProcessService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Run extends Command
{
    protected GitServiceInterface $git;
    protected ProcessServiceInterface $process;

    public function __construct(GitServiceInterface $git, ProcessService $process)
    {
        $this->git = $git;
        $this->process = $process;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Iterate through git commits to gather statistics')
            ->addArgument('branch', InputArgument::OPTIONAL, "The branch to use.", "master")
            ->addArgument('tasks', InputArgument::OPTIONAL, "The task file to use.", ".gitstats.php");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->checkEnvironment();

            $branch = $this->getBranch($input);
            $data = $this->runTasks(
                $this->getTasks($input),
                $this->git->getCommits($branch)
            );

            $output->write($this->format($data));
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return self::FAILURE;
        } finally {
            if (isset($branch)) {
                $this->git->checkout($branch);
            }
        }

        return self::SUCCESS;
    }

    private function checkEnvironment(): void
    {
        if (! $this->git->isGitAvailable()) {
            throw new \RuntimeException("Cannot proceed: unable to find git command.");
        }

        if (! $this->git->isGitRepository()) {
            throw new \RuntimeException(sprintf("Cannot proceed: %s is not a git repository.", getcwd()));
        }

        if (! $this->git->updateIndex()) {
            throw new \RuntimeException("Cannot proceed: unable to update index");
        }

        if ($this->git->hasUnstagedChanges()) {
            throw new \RuntimeException("Cannot proceed: you have unstaged changes." .
                "Please commit or stash them.");
        }

        if ($this->git->hasUncommittedChanges()) {
            throw new \RuntimeException("Cannot proceed: your index contains uncommitted changes." .
                "Please commit or stash them.");
        }
    }

    private function getBranch(InputInterface $input): string
    {
        $branch = $input->getArgument('branch');

        if (! $this->git->isValidBranch($branch)) {
            throw new \RuntimeException("Branch {$branch} is not valid.");
        }

        return $branch;
    }

    private function getTasks(InputInterface $input): array
    {
        $file = $input->getArgument('tasks');

        if (! is_readable($file)) {
            throw new \RuntimeException("File {$file} does not exists");
        }

        return (require $file)['tasks'] ?? [];
    }

    private function copyBranch(string $branch): string
    {
        $copyBranch = uniqid('gitstats-');

        if (! $this->git->copyBranch($branch, $copyBranch)) {
            throw new \RuntimeException("Unable to copy {$branch} to {$copyBranch}.");
        }

        return $copyBranch;
    }

    private function runTasks(array $tasks, iterable $commits): \Generator
    {
        // headers
        yield ['commit', 'date', ...array_keys($tasks)];

        foreach ($commits as $commit) {
            $this->git->checkout($commit);

            $timestamp = $this->git->getCommitTimestamp($commit);
            $data = [
                'commit' => $commit,
                'date' => date('Y-m-d H:i:s', $timestamp),
            ];

            foreach ($tasks as $name => $command) {
                if (is_array($command)) {
                    $output = $this->process->run($command)->getOutput();
                } elseif (is_string($command)) {
                    exec($command, $output);
                    $output = implode(PHP_EOL, $output);
                } elseif (is_callable($command)) {
                    $output = $command($this->git);
                }

                $data[$name] = trim($output);
            }

            yield $data;
        }
    }

    private function format(iterable $data): \Generator
    {
        $buffer = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            $length = fputcsv($buffer, $row, ',', '"', '\\');
            fseek($buffer, ftell($buffer) - $length);
            yield fgets($buffer);
        }

        fclose($buffer);
    }
}
