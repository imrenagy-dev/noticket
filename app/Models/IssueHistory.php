<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['issue_id', 'user_id', 'action', 'field', 'old_value', 'new_value'];

    protected $casts = ['created_at' => 'datetime'];

    public function issue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
