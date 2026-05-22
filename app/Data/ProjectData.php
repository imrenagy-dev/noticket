<?php

namespace App\Data;

use App\Models\Project;

readonly class ProjectData
{
    public function __construct(
        public int     $id,
        public string  $name,
        public string  $key,
        public ?string $description,
    ) {}

    public static function fromModel(Project $project): self
    {
        return new self(
            id:          $project->id,
            name:        $project->name,
            key:         $project->key,
            description: $project->description,
        );
    }
}
