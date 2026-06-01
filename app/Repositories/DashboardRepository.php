<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function __construct(
        private readonly Issue $issue,
        private readonly Sprint $sprint,
    ) {}

    public function getStats(Team $team, int $userId): array
    {
        $projectIds = $team->projects()->pluck('id');

        if ($projectIds->isEmpty()) {
            return ['projects' => 0, 'open_issues' => 0, 'my_issues' => 0, 'active_sprints' => 0];
        }

        return [
            'projects'       => $projectIds->count(),
            'open_issues'    => $this->issue->newQuery()->whereIn('project_id', $projectIds, 'and', false)->where('status', '!=', 'done')->count(),
            'my_issues'      => $this->issue->newQuery()->whereIn('project_id', $projectIds, 'and', false)->where('assignee_id', $userId)->where('status', '!=', 'done')->count(),
            'active_sprints' => $this->sprint->newQuery()->whereIn('project_id', $projectIds, 'and', false)->where('status', 'active')->count(),
        ];
    }

    public function getMyIssues(Team $team, int $userId, int $limit = 10): Collection
    {
        $projectIds = $team->projects()->pluck('id');

        if ($projectIds->isEmpty()) {
            return collect();
        }

        return $this->issue->newQuery()->whereIn('project_id', $projectIds, 'and', false)
            ->where('assignee_id', $userId)
            ->where('status', '!=', 'done')
            ->with(['project:id,name,key'])
            ->orderByRaw("FIELD(priority, 'highest', 'high', 'medium', 'low', 'lowest')")
            ->limit($limit)
            ->get();
    }

    public function getRecentIssues(Team $team, int $limit = 10): Collection
    {
        $projectIds = $team->projects()->pluck('id');

        if ($projectIds->isEmpty()) {
            return collect();
        }

        return $this->issue->newQuery()->whereIn('project_id', $projectIds, 'and', false)
            ->with(['project:id,name,key', 'assignee:id,name'])
            ->latest('updated_at')
            ->limit($limit)
            ->get();
    }
}
