<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Presenters\ProjectPresenterInterface;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function __construct(
        private ProjectPresenterInterface $presenter,
        private IssueRepositoryInterface  $issueRepo,
        private SprintRepositoryInterface $sprintRepo,
    ) {}

    public function show(Team $current_team, Project $project): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $activeSprint = $this->sprintRepo->findActive($project);

        $rawColumns = $activeSprint
            ? $this->issueRepo->boardColumns($activeSprint)
            : ['todo' => [], 'in_progress' => [], 'in_review' => [], 'done' => []];

        $columns = array_map(
            fn (array $issues) => array_map(
                fn (Issue $issue) => $this->presenter->issue($issue, $project->key),
                $issues,
            ),
            $rawColumns,
        );

        $plannedAndActive = $this->sprintRepo->forProject($project)
            ->filter(fn (Sprint $s) => in_array($s->status, ['planned', 'active']));

        return Inertia::render('projects/board', [
            'project'      => $this->presenter->project($project),
            'activeSprint' => $activeSprint ? $this->presenter->sprint($activeSprint) : null,
            'columns'      => $columns,
            'members'      => $this->presenter->members($current_team),
            'sprints'      => $plannedAndActive->map(fn (Sprint $s) => [
                'id'     => $s->id,
                'name'   => $s->name,
                'status' => $s->status,
            ])->values(),
        ]);
    }
}
