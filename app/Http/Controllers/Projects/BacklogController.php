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

class BacklogController extends Controller
{
    public function __construct(
        private ProjectPresenterInterface $presenter,
        private IssueRepositoryInterface  $issueRepo,
        private SprintRepositoryInterface $sprintRepo,
    ) {}

    public function show(Team $current_team, Project $project): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $sprints = $this->sprintRepo->forProject($project)
            ->map(fn (Sprint $sprint) => [
                ...$this->presenter->sprint($sprint),
                'issues' => $this->issueRepo->forSprint($sprint)
                    ->map(fn (Issue $issue) => $this->presenter->issue($issue, $project->key))
                    ->toArray(),
            ]);

        $backlog = $this->issueRepo->forBacklog($project)
            ->map(fn (Issue $issue) => $this->presenter->issue($issue, $project->key));

        return Inertia::render('projects/backlog', [
            'project' => $this->presenter->project($project),
            'sprints' => $sprints,
            'backlog' => $backlog,
            'members' => $this->presenter->members($current_team),
        ]);
    }
}
