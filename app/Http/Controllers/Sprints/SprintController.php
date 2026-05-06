<?php

namespace App\Http\Controllers\Sprints;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function store(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $sprintCount = $project->sprints()->count();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->sprints()->create([
            'name' => $validated['name'] ?? $project->key . ' Sprint ' . ($sprintCount + 1),
            'goal' => $validated['goal'] ?? null,
            'status' => 'planned',
        ]);

        return back();
    }

    public function update(Request $request, Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $sprint->update($validated);

        return back();
    }

    public function start(Request $request, Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);
        abort_if($sprint->status !== 'planned', 422);

        $hasActive = $project->sprints()->where('status', 'active')->exists();
        abort_if($hasActive, 422, 'A sprint is already active.');

        $sprint->update(['status' => 'active']);

        return back();
    }

    public function complete(Request $request, Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);
        abort_if($sprint->status !== 'active', 422);

        $sprint->update(['status' => 'completed']);

        // Move incomplete issues back to backlog
        $project->issues()
            ->where('sprint_id', $sprint->id)
            ->whereNotIn('status', ['done'])
            ->update(['sprint_id' => null]);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project, Sprint $sprint): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($sprint->project_id !== $project->id, 404);
        abort_if($sprint->status === 'active', 422, 'Cannot delete an active sprint.');

        // Move issues back to backlog
        $project->issues()
            ->where('sprint_id', $sprint->id)
            ->update(['sprint_id' => null]);

        $sprint->delete();

        return back();
    }
}
