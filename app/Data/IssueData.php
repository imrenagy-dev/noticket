<?php

namespace App\Data;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Models\Issue;
use Carbon\CarbonImmutable;

readonly class IssueData
{
    public function __construct(
        public int             $id,
        public int             $number,
        public string          $issueKey,
        public string          $title,
        public ?string         $description,
        public IssueType       $type,
        public IssueStatus     $status,
        public IssuePriority   $priority,
        public ?int            $storyPoints,
        public array           $checklist,
        public ?int            $sprintId,
        public ?MemberData     $reporter,
        public ?MemberData     $assignee,
        public int             $boardOrder,
        public int             $backlogOrder,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    public static function fromModel(Issue $issue, string $projectKey): self
    {
        return new self(
            id:           $issue->id,
            number:       $issue->number,
            issueKey:     $projectKey . '-' . $issue->number,
            title:        $issue->title,
            description:  $issue->description,
            type:         IssueType::from($issue->type),
            status:       IssueStatus::from($issue->status),
            priority:     IssuePriority::from($issue->priority),
            storyPoints:  $issue->story_points,
            checklist:    $issue->checklist ?? [],
            sprintId:     $issue->sprint_id,
            reporter:     $issue->reporter ? MemberData::fromModel($issue->reporter) : null,
            assignee:     $issue->assignee ? MemberData::fromModel($issue->assignee) : null,
            boardOrder:   $issue->board_order,
            backlogOrder: $issue->backlog_order,
            createdAt:    CarbonImmutable::instance($issue->created_at),
            updatedAt:    CarbonImmutable::instance($issue->updated_at),
        );
    }
}
