<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Presenters\ProjectPresenter;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class BacklogController extends Controller
{
    public function __construct(private ProjectPresenter $presenter) {}

    public function show(Team $current_team, Project $project): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $sprints = $project->sprints()
            ->whereIn('status', ['planned', 'active'])
            ->orderByRaw("FIELD(status, 'active', 'planned')")
            ->orderBy('created_at')
            ->get()
            ->map(fn (Sprint $sprint) => [
                ...$this->presenter->sprint($sprint),
                'issues' => $project->issues()
                    ->where('sprint_id', $sprint->id)
                    ->with(['reporter:id,name', 'assignee:id,name'])
                    ->orderBy('backlog_order')
                    ->get()
                    ->map(fn (Issue $issue) => $this->presenter->issue($issue, $project->key))
                    ->toArray(),
            ]);

        $backlog = $project->issues()
            ->whereNull('sprint_id')
            ->with(['reporter:id,name', 'assignee:id,name'])
            ->orderBy('backlog_order')
            ->get()
            ->map(fn (Issue $issue) => $this->presenter->issue($issue, $project->key));

        return Inertia::render('projects/backlog', [
            'project' => $this->presenter->project($project),
            'sprints' => $sprints,
            'backlog' => $backlog,
            'members' => $this->presenter->members($current_team),
        ]);
    }
}
