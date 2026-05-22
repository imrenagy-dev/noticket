<?php

namespace App\Http\Controllers\Sprints;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Services\SprintServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function __construct(private SprintServiceInterface $sprintService) {}

    public function store(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->sprintService->create($project, $validated['name'] ?? null, $validated['goal'] ?? null);

        return back();
    }

    public function update(Request $request, Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'goal'      => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->sprintService->update($sprint, $validated);

        return back();
    }

    public function start(Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);

        $this->sprintService->start($project, $sprint);

        return back();
    }

    public function complete(Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);

        $this->sprintService->complete($project, $sprint);

        return back();
    }

    public function destroy(Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);

        $this->sprintService->delete($project, $sprint);

        return back();
    }
}
