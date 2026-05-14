<?php

namespace App\Http\Presenters;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;

class ProjectPresenter
{
    public function project(Project $project): array
    {
        return [
            'id'          => $project->id,
            'name'        => $project->name,
            'key'         => $project->key,
            'description' => $project->description,
        ];
    }

    public function sprint(Sprint $sprint): array
    {
        return [
            'id'        => $sprint->id,
            'name'      => $sprint->name,
            'goal'      => $sprint->goal,
            'status'    => $sprint->status,
            'starts_at' => $sprint->starts_at?->toISOString(),
            'ends_at'   => $sprint->ends_at?->toISOString(),
        ];
    }

    public function issue(Issue $issue, string $projectKey): array
    {
        return [
            'id'            => $issue->id,
            'number'        => $issue->number,
            'issue_key'     => $projectKey . '-' . $issue->number,
            'title'         => $issue->title,
            'type'          => $issue->type,
            'status'        => $issue->status,
            'priority'      => $issue->priority,
            'story_points'  => $issue->story_points,
            'sprint_id'     => $issue->sprint_id,
            'board_order'   => $issue->board_order,
            'backlog_order' => $issue->backlog_order,
            'reporter'      => $issue->reporter ? ['id' => $issue->reporter->id, 'name' => $issue->reporter->name] : null,
            'assignee'      => $issue->assignee ? ['id' => $issue->assignee->id, 'name' => $issue->assignee->name] : null,
            'created_at'    => $issue->created_at->toISOString(),
            'updated_at'    => $issue->updated_at->toISOString(),
        ];
    }

    public function members(Team $team): array
    {
        return $team->members()
            ->select('users.id', 'users.name')
            ->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])
            ->toArray();
    }
}
