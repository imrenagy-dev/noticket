<?php

namespace App\Http\Controllers\Issues;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    public function store(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:epic,story,task,bug,subtask'],
            'priority' => ['required', 'in:lowest,low,medium,high,highest'],
            'status' => ['required', 'in:todo,in_progress,in_review,done'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'story_points' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (isset($validated['sprint_id'])) {
            $sprint = Sprint::find($validated['sprint_id']);
            abort_if($sprint?->project_id !== $project->id, 422);
        }

        $project->issues()->create([
            ...$validated,
            'reporter_id' => $request->user()->id,
        ]);

        return back();
    }

    public function show(Request $request, Team $current_team, Project $project, Issue $issue): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $issue->load([
            'reporter:id,name',
            'assignee:id,name',
            'sprint:id,name,status',
            'comments.user:id,name',
        ]);

        return Inertia::render('projects/issue', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'key' => $project->key,
            ],
            'issue' => [
                'id' => $issue->id,
                'number' => $issue->number,
                'issue_key' => $project->key . '-' . $issue->number,
                'title' => $issue->title,
                'description' => $issue->description,
                'checklist' => $issue->checklist ?? [],
                'type' => $issue->type,
                'status' => $issue->status,
                'priority' => $issue->priority,
                'story_points' => $issue->story_points,
                'sprint_id' => $issue->sprint_id,
                'sprint' => $issue->sprint ? ['id' => $issue->sprint->id, 'name' => $issue->sprint->name, 'status' => $issue->sprint->status] : null,
                'reporter' => $issue->reporter ? ['id' => $issue->reporter->id, 'name' => $issue->reporter->name] : null,
                'assignee' => $issue->assignee ? ['id' => $issue->assignee->id, 'name' => $issue->assignee->name] : null,
                'comments' => $issue->comments->map(fn (Comment $c) => [
                    'id' => $c->id,
                    'content' => $c->content,
                    'user' => ['id' => $c->user->id, 'name' => $c->user->name],
                    'created_at' => $c->created_at->toISOString(),
                    'updated_at' => $c->updated_at->toISOString(),
                ]),
                'created_at' => $issue->created_at->toISOString(),
                'updated_at' => $issue->updated_at->toISOString(),
            ],
            'members' => $current_team->members()
                ->select('users.id', 'users.name')
                ->get()
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name]),
            'sprints' => $project->sprints()
                ->whereIn('status', ['planned', 'active'])
                ->get(['id', 'name', 'status'])
                ->map(fn (Sprint $s) => ['id' => $s->id, 'name' => $s->name, 'status' => $s->status]),
        ]);
    }

    public function update(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:epic,story,task,bug,subtask'],
            'priority' => ['sometimes', 'in:lowest,low,medium,high,highest'],
            'status' => ['sometimes', 'in:todo,in_progress,in_review,done'],
            'description' => ['sometimes', 'nullable', 'string'],
            'checklist' => ['sometimes', 'nullable', 'array'],
            'checklist.*.id' => ['required_with:checklist', 'string'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:500'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
            'assignee_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'sprint_id' => ['sometimes', 'nullable', 'exists:sprints,id'],
            'story_points' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (array_key_exists('sprint_id', $validated) && $validated['sprint_id'] !== null) {
            $sprint = Sprint::find($validated['sprint_id']);
            abort_if($sprint?->project_id !== $project->id, 422);
        }

        $issue->update($validated);

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $issue->delete();

        return back();
    }
}
