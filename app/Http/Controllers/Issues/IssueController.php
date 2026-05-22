<?php

namespace App\Http\Controllers\Issues;

use App\Data\CommentDTO;
use App\Data\IssueHistoryDTO;
use App\Data\IssueSearchDTO;
use App\Data\SprintDTO;
use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Http\Controllers\Controller;
use App\Http\Presenters\ProjectPresenterInterface;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use App\Services\IssueServiceInterface;
use App\Services\SprintServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    public function __construct(
        private IssueServiceInterface     $issueService,
        private SprintServiceInterface    $sprintService,
        private ProjectPresenterInterface $presenter,
    ) {}

    public function index(Request $request, Team $current_team, Project $project): JsonResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $issues = $this->issueService->search($project, (string) $request->query('q', ''));

        return response()->json($issues->map(fn (IssueSearchDTO $i) => [
            'id'        => $i->id,
            'issue_key' => $i->issueKey,
            'title'     => $i->title,
            'checklist' => $i->checklist,
        ]));
    }

    public function store(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['required', Rule::enum(IssueType::class)],
            'priority'         => ['required', Rule::enum(IssuePriority::class)],
            'status'           => ['required', Rule::enum(IssueStatus::class)],
            'description'      => ['nullable', 'string'],
            'checklist'        => ['nullable', 'array'],
            'checklist.*.id'   => ['required_with:checklist', 'string'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:500'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
            'assignee_id'      => ['nullable', 'exists:users,id'],
            'sprint_id'        => ['nullable', 'exists:sprints,id'],
            'story_points'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $this->issueService->create($project, $validated, $request->user()->id);

        return back();
    }

    public function show(Team $current_team, Project $project, Issue $issue): Response
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $detail  = $this->issueService->loadDetail($issue, $project->key);
        $sprints = $this->sprintService->getPlannedAndActive($project);

        return Inertia::render('projects/issue', [
            'project' => $this->presenter->project($project),
            'issue'   => [
                'id'           => $detail->issue->id,
                'number'       => $detail->issue->number,
                'issue_key'    => $detail->issue->issueKey,
                'title'        => $detail->issue->title,
                'description'  => $detail->issue->description,
                'checklist'    => $detail->issue->checklist,
                'type'         => $detail->issue->type->value,
                'status'       => $detail->issue->status->value,
                'priority'     => $detail->issue->priority->value,
                'story_points' => $detail->issue->storyPoints,
                'sprint_id'    => $detail->issue->sprintId,
                'sprint'       => $detail->issue->sprint ? [
                    'id'     => $detail->issue->sprint->id,
                    'name'   => $detail->issue->sprint->name,
                    'status' => $detail->issue->sprint->status->value,
                ] : null,
                'reporter'  => $detail->issue->reporter  ? ['id' => $detail->issue->reporter->id,  'name' => $detail->issue->reporter->name]  : null,
                'assignee'  => $detail->issue->assignee  ? ['id' => $detail->issue->assignee->id,  'name' => $detail->issue->assignee->name]  : null,
                'comments'  => array_map(fn (CommentDTO $c) => [
                    'id'         => $c->id,
                    'content'    => $c->content,
                    'user'       => ['id' => $c->user->id, 'name' => $c->user->name],
                    'created_at' => $c->createdAt->toISOString(),
                    'updated_at' => $c->updatedAt->toISOString(),
                ], $detail->comments),
                'histories' => array_map(fn (IssueHistoryDTO $h) => [
                    'id'         => $h->id,
                    'user'       => ['id' => $h->user->id, 'name' => $h->user->name],
                    'action'     => $h->action,
                    'field'      => $h->field,
                    'old_value'  => $h->oldValue,
                    'new_value'  => $h->newValue,
                    'created_at' => $h->createdAt->toISOString(),
                ], $detail->histories),
                'created_at' => $detail->issue->createdAt->toISOString(),
                'updated_at' => $detail->issue->updatedAt->toISOString(),
            ],
            'members' => $this->presenter->members($current_team),
            'sprints' => $sprints->map(fn (SprintDTO $s) => ['id' => $s->id, 'name' => $s->name, 'status' => $s->status->value])->values(),
        ]);
    }

    public function update(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $validated = $request->validate([
            'title'            => ['sometimes', 'string', 'max:255'],
            'type'             => ['sometimes', Rule::enum(IssueType::class)],
            'priority'         => ['sometimes', Rule::enum(IssuePriority::class)],
            'status'           => ['sometimes', Rule::enum(IssueStatus::class)],
            'description'      => ['sometimes', 'nullable', 'string'],
            'checklist'        => ['sometimes', 'nullable', 'array'],
            'checklist.*.id'   => ['required_with:checklist', 'string'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:500'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
            'assignee_id'      => ['sometimes', 'nullable', 'exists:users,id'],
            'sprint_id'        => ['sometimes', 'nullable', 'exists:sprints,id'],
            'story_points'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $this->issueService->update($issue, $project, $validated, $request->user()->id);

        return back();
    }

    public function bulkUpdate(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'issue_ids'   => ['required', 'array', 'min:1'],
            'issue_ids.*' => ['integer', 'exists:issues,id'],
            'sprint_id'   => ['nullable', 'exists:sprints,id'],
        ]);

        $this->issueService->bulkUpdateSprint(
            $project,
            $validated['issue_ids'],
            $validated['sprint_id'] ?? null,
        );

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $this->issueService->delete($issue, $request->user()->id);

        return back();
    }
}
