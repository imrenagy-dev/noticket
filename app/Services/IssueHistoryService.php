<?php

namespace App\Services;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Models\Issue;
use App\Repositories\IssueHistoryRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Support\ChecklistDifferInterface;

class IssueHistoryService implements IssueHistoryServiceInterface
{
    public function __construct(
        private IssueHistoryRepositoryInterface $historyRepo,
        private UserRepositoryInterface         $users,
        private SprintRepositoryInterface       $sprints,
        private ChecklistDifferInterface        $checklistDiffer,
    ) {}

    public function recordCreated(Issue $issue, int $userId): void
    {
        $this->historyRepo->record($issue, [
            'user_id'   => $userId,
            'action'    => 'created',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    public function recordDeleted(Issue $issue, int $userId): void
    {
        $this->historyRepo->record($issue, [
            'user_id'   => $userId,
            'action'    => 'deleted',
            'field'     => null,
            'old_value' => null,
            'new_value' => null,
        ]);
    }

    public function computeUpdateEntries(Issue $issue, array $validated): array
    {
        $entries = [];

        foreach ($validated as $field => $newValue) {
            $oldRaw = $issue->getRawOriginal($field);

            $entry = match ($field) {
                'checklist'   => $this->checklistDiffer->entry($oldRaw, $newValue),
                'description' => $oldRaw !== $newValue
                    ? ['action' => 'updated', 'field' => 'description', 'old_value' => null, 'new_value' => null]
                    : null,
                'status'   => $this->enumEntry($field, $oldRaw, $newValue, IssueStatus::class),
                'type'     => $this->enumEntry($field, $oldRaw, $newValue, IssueType::class),
                'priority' => $this->enumEntry($field, $oldRaw, $newValue, IssuePriority::class),
                'assignee_id' => $this->resolvedEntry('assignee', $oldRaw, $newValue,
                    fn ($id) => $this->users->findNameById((int) $id)),
                'sprint_id' => $this->resolvedEntry('sprint', $oldRaw, $newValue,
                    fn ($id) => $this->sprints->findNameById((int) $id)),
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
            $this->historyRepo->record($issue, ['user_id' => $userId, ...$entry]);
        }
    }

    // --- private helpers ---

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

}
