<?php

namespace App\Services;

use App\Repositories\IssueRepositoryInterface;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

class IssueService
{
    public function __construct(
        private IssueRepositoryInterface $issues,
        private IssueHistoryInterface    $history,
    ) {}

    public function search(Project $project, string $query, int $limit = 30): Collection
    {
        return $this->issues->search($project, $query, $limit);
    }

    public function create(Project $project, array $data, int $reporterId): Issue
    {
        $issue = $this->issues->create($project, [
            ...$data,
            'reporter_id' => $reporterId,
        ]);

        $this->history->recordCreated($issue, $reporterId);

        return $issue;
    }

    public function update(Issue $issue, array $data, int $userId): Issue
    {
        $entries = $this->history->computeUpdateEntries($issue, $data);

        $issue = $this->issues->update($issue, $data);

        $this->history->persistUpdateEntries($issue, $entries, $userId);

        return $issue;
    }

    public function delete(Issue $issue, int $userId): void
    {
        $this->history->recordDeleted($issue, $userId);
        $this->issues->delete($issue);
    }

    public function bulkUpdateSprint(Project $project, array $issueIds, ?int $sprintId): void
    {
        if ($sprintId !== null) {
            $sprint = Sprint::find($sprintId);
            abort_if($sprint?->project_id !== $project->id, 422);
        }

        $this->issues->bulkUpdateSprint($project, $issueIds, $sprintId);
    }
}
