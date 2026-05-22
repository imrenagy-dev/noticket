<?php

namespace App\Services;

use App\Models\Issue;

interface IssueHistoryInterface
{
    public function recordCreated(Issue $issue, int $userId): void;

    public function recordDeleted(Issue $issue, int $userId): void;

    public function computeUpdateEntries(Issue $issue, array $validated): array;

    public function persistUpdateEntries(Issue $issue, array $entries, int $userId): void;
}
