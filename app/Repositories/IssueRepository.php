<?php

namespace App\Repositories;

use App\Repositories\IssueRepositoryInterface;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

class IssueRepository implements IssueRepositoryInterface
{
    public function search(Project $project, string $query, int $limit = 30): Collection
    {
        return $project->issues()
            ->select('id', 'number', 'title', 'checklist')
            ->when($query, fn ($q) => $q->where(function ($sub) use ($query) {
                $sub->where('title', 'like', "%{$query}%")
                    ->orWhere('number', 'like', "%{$query}%");
            }))
            ->orderBy('number', 'desc')
            ->limit($limit)
            ->get();
    }

    public function forBacklog(Project $project): Collection
    {
        return $project->issues()
            ->whereNull('sprint_id')
            ->with(['reporter:id,name', 'assignee:id,name'])
            ->orderBy('backlog_order')
            ->get();
    }

    public function forSprint(Sprint $sprint): Collection
    {
        return $sprint->project->issues()
            ->where('sprint_id', $sprint->id)
            ->with(['reporter:id,name', 'assignee:id,name'])
            ->orderBy('backlog_order')
            ->get();
    }

    public function boardColumns(Sprint $sprint): array
    {
        $columns = ['todo' => [], 'in_progress' => [], 'in_review' => [], 'done' => []];

        $sprint->project->issues()
            ->where('sprint_id', $sprint->id)
            ->with(['reporter:id,name', 'assignee:id,name'])
            ->orderBy('board_order')
            ->get()
            ->each(function (Issue $issue) use (&$columns) {
                $columns[$issue->status][] = $issue;
            });

        return $columns;
    }

    public function create(Project $project, array $data): Issue
    {
        return $project->issues()->create($data);
    }

    public function update(Issue $issue, array $data): Issue
    {
        $issue->update($data);

        return $issue->fresh();
    }

    public function delete(Issue $issue): void
    {
        $issue->delete();
    }

    public function bulkUpdateSprint(Project $project, array $issueIds, ?int $sprintId): void
    {
        $project->issues()
            ->whereIn('id', $issueIds)
            ->update(['sprint_id' => $sprintId]);
    }

    public function moveIncompleteToBacklog(Sprint $sprint): void
    {
        $sprint->project->issues()
            ->where('sprint_id', $sprint->id)
            ->where('status', '!=', 'done')
            ->update(['sprint_id' => null]);
    }

    public function moveAllToBacklog(Sprint $sprint): void
    {
        $sprint->project->issues()
            ->where('sprint_id', $sprint->id)
            ->update(['sprint_id' => null]);
    }

    public function loadDetail(Issue $issue): Issue
    {
        $issue->load([
            'reporter:id,name',
            'assignee:id,name',
            'sprint:id,name,status',
            'comments.user:id,name',
            'histories.user:id,name',
        ]);

        return $issue;
    }
}
