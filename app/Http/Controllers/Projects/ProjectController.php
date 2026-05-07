<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, Team $current_team): Response
    {
        $projects = $current_team->projects()
            ->withCount('issues')
            ->orderBy('name')
            ->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'key' => $p->key,
                'description' => $p->description,
                'issue_count' => $p->issues_count,
                'created_at' => $p->created_at->toISOString(),
            ]);

        return Inertia::render('projects/index', [
            'projects' => $projects,
        ]);
    }

    public function store(Request $request, Team $current_team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $keyExists = $current_team->projects()
            ->where('key', $validated['key'])
            ->exists();

        abort_if($keyExists, 422, 'Project key already exists in this team.');

        $project = $current_team->projects()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return to_route('projects.board', [
            'current_team' => $current_team->slug,
            'project' => $project->id,
        ]);
    }

    public function update(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->update($validated);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $project->delete();

        return to_route('projects.index', ['current_team' => $current_team->slug]);
    }
}
