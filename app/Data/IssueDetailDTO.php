<?php

namespace App\Data;

readonly class IssueDetailDTO
{
    public function __construct(
        public IssueDTO $issue,
        public array    $comments,   // CommentDTO[]
        public array    $histories,  // IssueHistoryDTO[]
    ) {}
}
