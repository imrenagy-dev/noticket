<?php

namespace App\Data;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Enums\IssueType;
use App\Models\Issue;
use Carbon\CarbonImmutable;

readonly class IssueDTO
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
        public ?MemberDTO      $reporter,
        public ?MemberDTO      $assignee,
        public int             $boardOrder,
        public int             $backlogOrder,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public ?SprintDTO      $sprint = null,
        public ?ProjectDTO     $project = null,
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
            reporter:     $issue->reporter ? MemberDTO::fromModel($issue->reporter) : null,
            assignee:     $issue->assignee ? MemberDTO::fromModel($issue->assignee) : null,
            boardOrder:   $issue->board_order,
            backlogOrder: $issue->backlog_order,
            createdAt:    CarbonImmutable::instance($issue->created_at),
            updatedAt:    CarbonImmutable::instance($issue->updated_at),
            sprint:       $issue->relationLoaded('sprint') && $issue->sprint
                            ? SprintDTO::fromModel($issue->sprint)
                            : null,
            project:      $issue->relationLoaded('project') && $issue->project
                            ? ProjectDTO::fromModel($issue->project)
                            : null,
        );
    }
}
