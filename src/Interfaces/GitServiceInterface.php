<?php

namespace Bdelespierre\GitStats\Interfaces;

interface GitServiceInterface
{
    public function isGitAvailable(): bool;

    public function isGitRepository(): bool;

    public function isWorkTreeClean(?string &$reason = null): bool;

    public function isValidBranch(string $branch): bool;

    public function checkout(string $commit): bool;

    public function getCommits(string $branch): iterable;

    public function getCommitTimestamp(string $commit): int;
}
