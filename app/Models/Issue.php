<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['project_id', 'sprint_id', 'parent_id', 'reporter_id', 'assignee_id', 'type', 'status', 'priority', 'title', 'description', 'checklist', 'story_points', 'board_order', 'backlog_order'])]
class Issue extends Model
{
    use SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Issue $issue) {
            $issue->number = (Issue::withTrashed()->where('project_id', $issue->project_id)->max('number') ?? 0) + 1;
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
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Issue::class, 'parent_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(IssueHistory::class)->latest('created_at');
    }

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'story_points' => 'integer',
            'board_order' => 'integer',
            'backlog_order' => 'integer',
        ];
    }
}
