<?php

namespace App\Http\Controllers\Issues;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\IssueHistory;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    private const STATUS_LABELS   = ['todo' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'done' => 'Done'];
    private const TYPE_LABELS     = ['epic' => 'Epic', 'story' => 'Story', 'task' => 'Task', 'bug' => 'Bug', 'subtask' => 'Subtask'];
    private const PRIORITY_LABELS = ['lowest' => 'Lowest', 'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'highest' => 'Highest'];

    public function store(Request $request, Team $current_team, Project $project): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['required', 'in:epic,story,task,bug,subtask'],
            'priority'         => ['required', 'in:lowest,low,medium,high,highest'],
            'status'           => ['required', 'in:todo,in_progress,in_review,done'],
            'description'      => ['nullable', 'string'],
            'checklist'        => ['nullable', 'array'],
            'checklist.*.id'   => ['required_with:checklist', 'string'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:500'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
            'assignee_id'      => ['nullable', 'exists:users,id'],
            'sprint_id'        => ['nullable', 'exists:sprints,id'],
            'story_points'     => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (isset($validated['sprint_id'])) {
            $sprint = Sprint::find($validated['sprint_id']);
            abort_if($sprint?->project_id !== $project->id, 422);
        }

        $issue = $project->issues()->create([
            ...$validated,
            'reporter_id' => $request->user()->id,
        ]);

        $issue->histories()->create([
            'user_id'   => $request->user()->id,
            'action'    => 'created',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
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
            'histories.user:id,name',
        ]);

        return Inertia::render('projects/issue', [
            'project' => [
                'id'   => $project->id,
                'name' => $project->name,
                'key'  => $project->key,
            ],
            'issue' => [
                'id'           => $issue->id,
                'number'       => $issue->number,
                'issue_key'    => $project->key . '-' . $issue->number,
                'title'        => $issue->title,
                'description'  => $issue->description,
                'checklist'    => $issue->checklist ?? [],
                'type'         => $issue->type,
                'status'       => $issue->status,
                'priority'     => $issue->priority,
                'story_points' => $issue->story_points,
                'sprint_id'    => $issue->sprint_id,
                'sprint'       => $issue->sprint ? ['id' => $issue->sprint->id, 'name' => $issue->sprint->name, 'status' => $issue->sprint->status] : null,
                'reporter'     => $issue->reporter ? ['id' => $issue->reporter->id, 'name' => $issue->reporter->name] : null,
                'assignee'     => $issue->assignee ? ['id' => $issue->assignee->id, 'name' => $issue->assignee->name] : null,
                'comments'     => $issue->comments->map(fn (Comment $c) => [
                    'id'         => $c->id,
                    'content'    => $c->content,
                    'user'       => ['id' => $c->user->id, 'name' => $c->user->name],
                    'created_at' => $c->created_at->toISOString(),
                    'updated_at' => $c->updated_at->toISOString(),
                ]),
                'histories'    => $issue->histories->map(fn (IssueHistory $h) => [
                    'id'         => $h->id,
                    'user'       => ['id' => $h->user->id, 'name' => $h->user->name],
                    'action'     => $h->action,
                    'field'      => $h->field,
                    'old_value'  => $h->old_value,
                    'new_value'  => $h->new_value,
                    'created_at' => $h->created_at->toISOString(),
                ]),
                'created_at'   => $issue->created_at->toISOString(),
                'updated_at'   => $issue->updated_at->toISOString(),
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
            'title'            => ['sometimes', 'string', 'max:255'],
            'type'             => ['sometimes', 'in:epic,story,task,bug,subtask'],
            'priority'         => ['sometimes', 'in:lowest,low,medium,high,highest'],
            'status'           => ['sometimes', 'in:todo,in_progress,in_review,done'],
            'description'      => ['sometimes', 'nullable', 'string'],
            'checklist'        => ['sometimes', 'nullable', 'array'],
            'checklist.*.id'   => ['required_with:checklist', 'string'],
            'checklist.*.text' => ['required_with:checklist', 'string', 'max:500'],
            'checklist.*.done' => ['required_with:checklist', 'boolean'],
            'assignee_id'      => ['sometimes', 'nullable', 'exists:users,id'],
            'sprint_id'        => ['sometimes', 'nullable', 'exists:sprints,id'],
            'story_points'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if (array_key_exists('sprint_id', $validated) && $validated['sprint_id'] !== null) {
            $sprint = Sprint::find($validated['sprint_id']);
            abort_if($sprint?->project_id !== $project->id, 422);
        }

        $histories = $this->buildUpdateHistories($issue, $validated);

        $issue->update($validated);

        foreach ($histories as $entry) {
            $issue->histories()->create(['user_id' => $request->user()->id, ...$entry]);
        }

        return back();
    }

    public function destroy(Request $request, Team $current_team, Project $project, Issue $issue): RedirectResponse
    {
        abort_if($project->team_id !== $current_team->id, 404);
        abort_if($issue->project_id !== $project->id, 404);

        $issue->histories()->create([
            'user_id'   => $request->user()->id,
            'action'    => 'deleted',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
        ]);

        $issue->delete();

        return back();
    }

    private function checklistSummary(array $items): string
    {
        if (empty($items)) return 'empty';
        $done  = count(array_filter($items, fn($i) => $i['done']));
        $total = count($items);
        return $total . ' item' . ($total !== 1 ? 's' : '') . ' (' . $done . ' done)';
    }

    private function checklistDiff(array $old, array $new): array
    {
        $oldById = array_column($old, null, 'id');
        $newById = array_column($new, null, 'id');

        $changes = [];

        foreach ($new as $item) {
            if (!isset($oldById[$item['id']])) {
                $changes[] = ['type' => 'added', 'text' => $item['text']];
            }
        }
        foreach ($old as $item) {
            if (!isset($newById[$item['id']])) {
                $changes[] = ['type' => 'removed', 'text' => $item['text']];
            }
        }
        foreach ($new as $item) {
            if (isset($oldById[$item['id']])) {
                $o = $oldById[$item['id']];
                if ($o['done'] !== $item['done']) {
                    $changes[] = ['type' => $item['done'] ? 'checked' : 'unchecked', 'text' => $item['text']];
                } elseif ($o['text'] !== $item['text']) {
                    $changes[] = ['type' => 'renamed', 'old_text' => $o['text'], 'new_text' => $item['text']];
                }
            }
        }

        if (count($changes) === 1) {
            $c = $changes[0];
            return match ($c['type']) {
                'added'     => [null, $c['text']],
                'removed'   => [$c['text'], null],
                'checked'   => ['☐ ' . $c['text'], '☑ ' . $c['text']],
                'unchecked' => ['☑ ' . $c['text'], '☐ ' . $c['text']],
                'renamed'   => [$c['old_text'], $c['new_text']],
                default     => [$this->checklistSummary($old), $this->checklistSummary($new)],
            };
        }

        return [$this->checklistSummary($old), $this->checklistSummary($new)];
    }

    private function buildUpdateHistories(Issue $issue, array $validated): array
    {
        $entries = [];

        foreach ($validated as $field => $newValue) {
            $oldRaw = $issue->getRawOriginal($field);

            match ($field) {
                'checklist' => (function () use (&$entries, $oldRaw, $newValue) {
                    $oldDecoded = $oldRaw ? json_decode($oldRaw, true) : [];
                    $newDecoded = $newValue ?? [];
                    if (json_encode($oldDecoded) !== json_encode($newDecoded)) {
                        [$oldDisplay, $newDisplay] = $this->checklistDiff($oldDecoded, $newDecoded);
                        $entries[] = ['action' => 'updated', 'field' => 'checklist', 'old_value' => $oldDisplay, 'new_value' => $newDisplay];
                    }
                })(),

                'description' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ($oldRaw !== $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'description', 'old_value' => null, 'new_value' => null];
                    }
                })(),

                'status' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ($oldRaw !== $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'status',
                            'old_value' => self::STATUS_LABELS[$oldRaw] ?? $oldRaw,
                            'new_value' => self::STATUS_LABELS[$newValue] ?? $newValue,
                        ];
                    }
                })(),

                'type' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ($oldRaw !== $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'type',
                            'old_value' => self::TYPE_LABELS[$oldRaw] ?? $oldRaw,
                            'new_value' => self::TYPE_LABELS[$newValue] ?? $newValue,
                        ];
                    }
                })(),

                'priority' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ($oldRaw !== $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'priority',
                            'old_value' => self::PRIORITY_LABELS[$oldRaw] ?? $oldRaw,
                            'new_value' => self::PRIORITY_LABELS[$newValue] ?? $newValue,
                        ];
                    }
                })(),

                'assignee_id' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ((string) $oldRaw !== (string) $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'assignee',
                            'old_value' => $oldRaw ? User::find($oldRaw)?->name : null,
                            'new_value' => $newValue ? User::find($newValue)?->name : null,
                        ];
                    }
                })(),

                'sprint_id' => (function () use (&$entries, $oldRaw, $newValue) {
                    if ((string) $oldRaw !== (string) $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => 'sprint',
                            'old_value' => $oldRaw ? Sprint::find($oldRaw)?->name : null,
                            'new_value' => $newValue ? Sprint::find($newValue)?->name : null,
                        ];
                    }
                })(),

                'title', 'story_points' => (function () use (&$entries, $field, $oldRaw, $newValue) {
                    if ((string) $oldRaw !== (string) $newValue) {
                        $entries[] = ['action' => 'updated', 'field' => $field,
                            'old_value' => $oldRaw !== null ? (string) $oldRaw : null,
                            'new_value' => $newValue !== null ? (string) $newValue : null,
                        ];
                    }
                })(),

                default => null,
            };
        }

        return $entries;
    }
}
