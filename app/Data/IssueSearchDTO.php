<?php

namespace App\Data;

use App\Models\Issue;

readonly class IssueSearchDTO
{
    public function __construct(
        public int    $id,
        public string $issueKey,
        public string $title,
        public array  $checklist,
    ) {}

    public static function fromModel(Issue $issue, string $projectKey): self
    {
        return new self(
            id:        $issue->id,
            issueKey:  $projectKey . '-' . $issue->number,
            title:     $issue->title,
            checklist: $issue->checklist ?? [],
        );
    }
}
