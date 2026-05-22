<?php

namespace App\Repositories;

use App\Models\Issue;

interface IssueHistoryRepositoryInterface
{
    public function record(Issue $issue, array $data): void;
}
