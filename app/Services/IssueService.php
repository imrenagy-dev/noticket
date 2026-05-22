<?php

namespace App\Services;

use App\Data\CommentDTO;
use App\Data\IssueDTO;
use App\Data\IssueDetailDTO;
use App\Data\IssueHistoryDTO;
use App\Data\IssueSearchDTO;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\IssueHistory;
use App\Models\Project;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use Illuminate\Support\Collection;

class IssueService implements IssueServiceInterface
{
    public function __construct(
        private IssueRepositoryInterface  $issues,
        private IssueHistoryServiceInterface     $history,
        private SprintRepositoryInterface $sprints,
    ) {}

    public function search(Project $project, string $query, int $limit = 30): Collection
    {
        return $this->issues->search($project, $query, $limit)
            ->map(fn (Issue $i) => IssueSearchDTO::fromModel($i, $project->key));
    }

    public function loadDetail(Issue $issue, string $projectKey): IssueDetailDTO
    {
        $issue = $this->issues->loadDetail($issue);

        return new IssueDetailDTO(
            issue:     IssueDTO::fromModel($issue, $projectKey),
            comments:  $issue->comments->map(fn (Comment $c) => CommentDTO::fromModel($c))->all(),
            histories: $issue->histories->map(fn (IssueHistory $h) => IssueHistoryDTO::fromModel($h))->all(),
        );
    }

    public function create(Project $project, array $data, int $reporterId): IssueDTO
    {
        $this->validateSprint($data['sprint_id'] ?? null, $project);

        $issue = $this->issues->create($project, [
            ...$data,
            'reporter_id' => $reporterId,
        ]);

        $this->history->recordCreated($issue, $reporterId);

        return IssueDTO::fromModel($issue, $project->key);
    }

    public function update(Issue $issue, Project $project, array $data, int $userId): IssueDTO
    {
        if (array_key_exists('sprint_id', $data)) {
            $this->validateSprint($data['sprint_id'], $project);
        }

        $entries = $this->history->computeUpdateEntries($issue, $data);
        $issue   = $this->issues->update($issue, $data);
        $this->history->persistUpdateEntries($issue, $entries, $userId);

        return IssueDTO::fromModel($issue, $project->key);
    }

    public function delete(Issue $issue, int $userId): void
    {
        $this->history->recordDeleted($issue, $userId);
        $this->issues->delete($issue);
    }

    public function bulkUpdateSprint(Project $project, array $issueIds, ?int $sprintId): void
    {
        $this->validateSprint($sprintId, $project);
        $this->issues->bulkUpdateSprint($project, $issueIds, $sprintId);
    }

    private function validateSprint(?int $sprintId, Project $project): void
    {
        if ($sprintId === null) {
            return;
        }

        $sprint = $this->sprints->findById($sprintId);
        abort_if($sprint?->project_id !== $project->id, 422);
    }
}
