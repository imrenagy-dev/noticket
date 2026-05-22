<?php

namespace App\Services;

use App\Data\IssueDTO;
use App\Models\Issue;
use App\Models\Team;
use App\Repositories\DashboardRepositoryInterface;
use Illuminate\Support\Collection;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(private DashboardRepositoryInterface $dashboard) {}

    public function getStats(Team $team, int $userId): array
    {
        return $this->dashboard->getStats($team, $userId);
    }

    public function getMyIssues(Team $team, int $userId): Collection
    {
        return $this->dashboard->getMyIssues($team, $userId)
            ->map(fn (Issue $i) => IssueDTO::fromModel($i, $i->project->key));
    }

    public function getRecentIssues(Team $team): Collection
    {
        return $this->dashboard->getRecentIssues($team)
            ->map(fn (Issue $i) => IssueDTO::fromModel($i, $i->project->key));
    }
}
