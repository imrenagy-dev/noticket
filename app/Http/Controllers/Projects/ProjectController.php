<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Models\Project;
use App\Models\Team;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(Team $current_team): Response
    {
        $projects = $this->projectService->listForTeam($current_team);

        return Inertia::render('projects/index', [
            'projects' => $projects->map(fn (Project $p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'key'         => $p->key,
                'description' => $p->description,
                'issue_count' => $p->issues_count,
                'created_at'  => $p->created_at->toISOString(),
            ]),
        ]);
    }

    public function store(StoreProjectRequest $request, Team $current_team): RedirectResponse
    {
        $project = $this->projectService->create($current_team, $request->validated(), $request->user()->id);

        return to_route('projects.board', [
            'current_team' => $current_team->slug,
            'project'      => $project->id,
        ]);
    }

    public function update(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->projectService->update($project, $validated);

        return back();
    }

    public function destroy(Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $this->projectService->delete($project);

        return to_route('projects.index', ['current_team' => $current_team->slug]);
    }
}
