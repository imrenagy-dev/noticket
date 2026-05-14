<?php

namespace App\Services;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Models\Issue;
use App\Models\Sprint;
use App\Models\User;

class IssueHistoryService
{
    public function recordCreated(Issue $issue, int $userId): void
    {
        $issue->histories()->create([
            'user_id'   => $userId,
            'action'    => 'created',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    public function recordDeleted(Issue $issue, int $userId): void
    {
        $issue->histories()->create([
            'user_id'   => $userId,
            'action'    => 'deleted',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    /**
     * Compute diff entries from original values — call BEFORE $issue->update().
     * Then pass the result to persistUpdateEntries() AFTER the update.
     */
    public function computeUpdateEntries(Issue $issue, array $validated): array
    {
        $entries = [];

        foreach ($validated as $field => $newValue) {
            $oldRaw = $issue->getRawOriginal($field);

            $entry = match ($field) {
                'checklist'   => $this->checklistEntry($oldRaw, $newValue),
                'description' => $oldRaw !== $newValue
                    ? ['action' => 'updated', 'field' => 'description', 'old_value' => null, 'new_value' => null]
                    : null,
                'status'   => $this->enumEntry($field, $oldRaw, $newValue, IssueStatus::class),
                'type'     => $this->enumEntry($field, $oldRaw, $newValue, IssueType::class),
                'priority' => $this->enumEntry($field, $oldRaw, $newValue, IssuePriority::class),
                'assignee_id' => $this->resolvedEntry('assignee', $oldRaw, $newValue,
                    fn ($id) => User::find($id)?->name),
                'sprint_id' => $this->resolvedEntry('sprint', $oldRaw, $newValue,
                    fn ($id) => Sprint::find($id)?->name),
                'title', 'story_points' => (string) $oldRaw !== (string) $newValue
                    ? ['action' => 'updated', 'field' => $field,
                        'old_value' => $oldRaw !== null ? (string) $oldRaw : null,
                        'new_value' => $newValue !== null ? (string) $newValue : null]
                    : null,
                default => null,
            };

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    public function persistUpdateEntries(Issue $issue, array $entries, int $userId): void
    {
        foreach ($entries as $entry) {
            $issue->histories()->create(['user_id' => $userId, ...$entry]);
        }
    }

    // --- private helpers ---

    private function checklistEntry(?string $oldRaw, mixed $newValue): ?array
    {
        $old = $oldRaw ? json_decode($oldRaw, true) : [];
        $new = $newValue ?? [];

        if (json_encode($old) === json_encode($new)) {
            return null;
        }

        [$oldDisplay, $newDisplay] = $this->checklistDiff($old, $new);

        return ['action' => 'updated', 'field' => 'checklist',
            'old_value' => $oldDisplay, 'new_value' => $newDisplay];
    }

    /** @param class-string<\BackedEnum> $enumClass */
    private function enumEntry(string $field, mixed $oldRaw, mixed $newValue, string $enumClass): ?array
    {
        if ($oldRaw === $newValue) {
            return null;
        }

        return ['action' => 'updated', 'field' => $field,
            'old_value' => $oldRaw ? ($enumClass::tryFrom($oldRaw)?->label() ?? $oldRaw) : null,
            'new_value' => $newValue ? ($enumClass::tryFrom($newValue)?->label() ?? $newValue) : null,
        ];
    }

    private function resolvedEntry(string $field, mixed $oldRaw, mixed $newValue, callable $resolve): ?array
    {
        if ((string) $oldRaw === (string) $newValue) {
            return null;
        }

        return ['action' => 'updated', 'field' => $field,
            'old_value' => $oldRaw ? $resolve($oldRaw) : null,
            'new_value' => $newValue ? $resolve($newValue) : null,
        ];
    }

    private function checklistDiff(array $old, array $new): array
    {
        $oldById = array_column($old, null, 'id');
        $newById = array_column($new, null, 'id');
        $changes = [];

        foreach ($new as $item) {
            if (! isset($oldById[$item['id']])) {
                $changes[] = ['type' => 'added', 'text' => $item['text']];
            }
        }
        foreach ($old as $item) {
            if (! isset($newById[$item['id']])) {
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

    private function checklistSummary(array $items): string
    {
        if (empty($items)) {
            return 'empty';
        }
        $done  = count(array_filter($items, fn ($i) => $i['done']));
        $total = count($items);
        return $total . ' item' . ($total !== 1 ? 's' : '') . ' (' . $done . ' done)';
    }
}
