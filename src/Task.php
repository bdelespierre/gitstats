<?php

namespace Bdelespierre\GitStats;

use Bdelespierre\GitStats\Interfaces\ProcessServiceInterface;

class Task
{
    protected ProcessServiceInterface $process;
    protected string $name;
    protected $command;
    protected array $patterns = [];
    protected string $output;

    public function __construct(
        ProcessServiceInterface $process,
        string $name,
        $command,
        array $patterns
    ) {
        $this->process = $process;

        $this->setName($name);
        $this->setCommand($command);
        $this->setPatterns($patterns);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHeaders(): array
    {
        if (empty($this->patterns)) {
            return [$this->getName()];
        }

        $headers = [];

        foreach ($this->patterns as $name => $regex) {
            $headers[] = "{$this->getName()}:{$name}";
        }

        return $headers;
    }

    public function run(...$args): self
    {
        if (is_array($this->command)) {
            $output = $this->process->run($this->command)->getOutput();
        } elseif (is_callable($this->command)) {
            $command = $this->command;
            $output = $command(...$args);
        } elseif (is_string($this->command)) {
            $output = $this->process->exec($this->command);
        }

        $this->output = trim($output);

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function toArray()
    {

        if (empty($this->patterns)) {
            return [
                $this->getName() => $this->getOutput()
            ];
        }

        $array = [];

        foreach ($this->patterns as $name => $pattern) {
            $array["{$this->getName()}:{$name}"] = $this->match($pattern);
        }

        return $array;
    }

    private function setName(string $name)
    {
        $this->name = $name;
    }

    private function setCommand($command)
    {
        if (! is_array($command) && ! is_callable($command) && ! is_string($command)) {
            throw new \InvalidArgumentException("Invalid command: '{$command}'.");
        }

        $this->command = $command;
    }

    private function setPatterns(array $patterns)
    {
        foreach ($patterns as $regex) {
            if (false === @preg_match($regex, null)) {
                throw new \InvalidArgumentException("Invalid regex: '{$regex}'.");
            }
        }

        $this->patterns = $patterns;
    }

    private function match(string $regex)
    {
        $match = preg_match($regex, $this->getOutput(), $matches);

        // if the pattern contains a capturing group
        if (isset($matches[1])) {
            return $matches[1];
        }

        // did the regex match output?
        return $match ? true : false;
    }
}
