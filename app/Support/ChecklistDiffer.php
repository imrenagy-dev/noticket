<?php

namespace App\Support;

class ChecklistDiffer implements ChecklistDifferInterface
{
    public function entry(?string $oldRaw, mixed $newValue): ?array
    {
        $old = $oldRaw ? json_decode($oldRaw, true) : [];
        $new = $newValue ?? [];

        if (json_encode($old) === json_encode($new)) {
            return null;
        }

        [$oldDisplay, $newDisplay] = $this->diff($old, $new);

        return ['action' => 'updated', 'field' => 'checklist',
            'old_value' => $oldDisplay, 'new_value' => $newDisplay];
    }

    private function diff(array $old, array $new): array
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
                default     => [$this->summary($old), $this->summary($new)],
            };
        }

        return [$this->summary($old), $this->summary($new)];
    }

    private function summary(array $items): string
    {
        if (empty($items)) {
            return 'empty';
        }
        $done  = count(array_filter($items, fn ($i) => $i['done']));
        $total = count($items);
        return $total . ' item' . ($total !== 1 ? 's' : '') . ' (' . $done . ' done)';
    }
}
