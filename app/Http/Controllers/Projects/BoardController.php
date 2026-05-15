<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Presenters\ProjectPresenter;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function __construct(private ProjectPresenter $presenter) {}

    public function show(Team $current_team, Project $project): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $activeSprint = $project->sprints()->where('status', 'active')->first();

        $columns = ['todo' => [], 'in_progress' => [], 'in_review' => [], 'done' => []];

        if ($activeSprint) {
            $project->issues()
                ->where('sprint_id', $activeSprint->id)
                ->with(['reporter:id,name', 'assignee:id,name'])
                ->orderBy('board_order')
                ->get()
                ->each(function ($issue) use (&$columns, $project) {
                    $columns[$issue->status][] = $this->presenter->issue($issue, $project->key);
                });
        }

        return Inertia::render('projects/board', [
            'project'      => $this->presenter->project($project),
            'activeSprint' => $activeSprint ? $this->presenter->sprint($activeSprint) : null,
            'columns'      => $columns,
            'members'      => $this->presenter->members($current_team),
            'sprints'      => $project->sprints()
                ->whereIn('status', ['planned', 'active'])
                ->get(['id', 'name', 'status'])
                ->map(fn (Sprint $s) => ['id' => $s->id, 'name' => $s->name, 'status' => $s->status]),
        ]);
    }
}
