<?php

namespace App\Data;

use App\Models\Project;
use Carbon\CarbonImmutable;

readonly class ProjectDTO
{
    public function __construct(
        public int              $id,
        public string           $name,
        public string           $key,
        public ?string          $description,
        public ?int             $issuesCount = null,
        public ?CarbonImmutable $createdAt = null,
    ) {}

    public static function fromModel(Project $project): self
    {
        return new self(
            id:          $project->id,
            name:        $project->name,
            key:         $project->key,
            description: $project->description,
            issuesCount: isset($project->issues_count) ? (int) $project->issues_count : null,
            createdAt:   $project->created_at ? CarbonImmutable::instance($project->created_at) : null,
        );
    }
}
