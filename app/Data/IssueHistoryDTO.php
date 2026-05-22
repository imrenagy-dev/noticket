<?php

namespace App\Data;

use App\Models\IssueHistory;
use Carbon\CarbonImmutable;

readonly class IssueHistoryDTO
{
    public function __construct(
        public int             $id,
        public MemberDTO       $user,
        public string          $action,
        public ?string         $field,
        public ?string         $oldValue,
        public ?string         $newValue,
        public CarbonImmutable $createdAt,
    ) {}

    public static function fromModel(IssueHistory $history): self
    {
        return new self(
            id:        $history->id,
            user:      MemberDTO::fromModel($history->user),
            action:    $history->action,
            field:     $history->field,
            oldValue:  $history->old_value,
            newValue:  $history->new_value,
            createdAt: CarbonImmutable::instance($history->created_at),
        );
    }
}
