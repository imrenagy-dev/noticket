<?php

namespace App\Observers;

use App\Models\Issue;

class IssueObserver
{
    public function creating(Issue $issue): void
    {
        $issue->number = (Issue::withTrashed()
            ->where('project_id', $issue->project_id)
            ->max('number') ?? 0) + 1;

        if ($issue->board_order === 0) {
            $issue->board_order = (Issue::where('project_id', $issue->project_id)
                ->where('status', $issue->status)
                ->max('board_order') ?? 0) + 1;
        }

        if ($issue->backlog_order === 0) {
            $issue->backlog_order = (Issue::where('project_id', $issue->project_id)
                ->whereNull('sprint_id')
                ->max('backlog_order') ?? 0) + 1;
        }
    }
}
