<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function show(Request $request, Team $current_team, Project $project): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $activeSprint = $project->sprints()->where('status', 'active')->first();

        $columns = ['todo' => [], 'in_progress' => [], 'in_review' => [], 'done' => []];

        if ($activeSprint) {
            $issues = $project->issues()
                ->where('sprint_id', $activeSprint->id)
                ->with(['reporter:id,name', 'assignee:id,name'])
                ->orderBy('board_order')
                ->get();

            foreach ($issues as $issue) {
                $columns[$issue->status][] = $this->formatIssue($issue, $project->key);
            }
        }

        return Inertia::render('projects/board', [
            'project' => $this->formatProject($project),
            'activeSprint' => $activeSprint ? $this->formatSprint($activeSprint) : null,
            'columns' => $columns,
            'members' => $this->formatMembers($current_team),
            'sprints' => $project->sprints()
                ->whereIn('status', ['planned', 'active'])
                ->get(['id', 'name', 'status'])
                ->map(fn (Sprint $s) => ['id' => $s->id, 'name' => $s->name, 'status' => $s->status]),
        ]);
    }

    private function formatProject(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'key' => $project->key,
            'description' => $project->description,
        ];
    }

    private function formatSprint(Sprint $sprint): array
    {
        return [
            'id' => $sprint->id,
            'name' => $sprint->name,
            'goal' => $sprint->goal,
            'status' => $sprint->status,
            'starts_at' => $sprint->starts_at?->toISOString(),
            'ends_at' => $sprint->ends_at?->toISOString(),
        ];
    }

    private function formatIssue(Issue $issue, string $projectKey): array
    {
        return [
            'id' => $issue->id,
            'number' => $issue->number,
            'issue_key' => $projectKey . '-' . $issue->number,
            'title' => $issue->title,
            'type' => $issue->type,
            'status' => $issue->status,
            'priority' => $issue->priority,
            'story_points' => $issue->story_points,
            'sprint_id' => $issue->sprint_id,
            'board_order' => $issue->board_order,
            'backlog_order' => $issue->backlog_order,
            'reporter' => $issue->reporter ? ['id' => $issue->reporter->id, 'name' => $issue->reporter->name] : null,
            'assignee' => $issue->assignee ? ['id' => $issue->assignee->id, 'name' => $issue->assignee->name] : null,
            'created_at' => $issue->created_at->toISOString(),
            'updated_at' => $issue->updated_at->toISOString(),
        ];
    }

    private function formatMembers(Team $team): array
    {
        return $team->members()
            ->select('users.id', 'users.name')
            ->get()
            ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])
            ->toArray();
    }
}
