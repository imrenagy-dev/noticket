<?php

namespace App\Repositories;

use App\Models\Issue;

class IssueHistoryRepository implements IssueHistoryRepositoryInterface
{
    public function record(Issue $issue, array $data): void
    {
        $issue->histories()->create($data);
    }
}
