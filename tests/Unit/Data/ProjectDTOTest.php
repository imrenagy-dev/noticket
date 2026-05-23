<?php

namespace Tests\Unit\Data;

use App\Data\ProjectDTO;
use App\Models\Project;
use Tests\TestCase;

class ProjectDTOTest extends TestCase
{
    public function test_from_model_maps_required_fields(): void
    {
        $project = (new Project())->forceFill([
            'id'          => 10,
            'name'        => 'My Project',
            'key'         => 'MYP',
            'description' => 'A description',
        ]);

        $dto = ProjectDTO::fromModel($project);

        $this->assertSame(10, $dto->id);
        $this->assertSame('My Project', $dto->name);
        $this->assertSame('MYP', $dto->key);
        $this->assertSame('A description', $dto->description);
        $this->assertNull($dto->issuesCount);
        $this->assertNull($dto->createdAt);
    }

    public function test_from_model_maps_issues_count_when_present(): void
    {
        $project = (new Project())->forceFill([
            'id'          => 1,
            'name'        => 'P',
            'key'         => 'P',
            'description' => null,
            'issues_count' => 7,
        ]);

        $dto = ProjectDTO::fromModel($project);

        $this->assertSame(7, $dto->issuesCount);
    }

    public function test_from_model_maps_created_at_when_present(): void
    {
        $project = (new Project())->forceFill([
            'id'          => 1,
            'name'        => 'P',
            'key'         => 'P',
            'description' => null,
            'created_at'  => '2024-06-01 00:00:00',
        ]);

        $dto = ProjectDTO::fromModel($project);

        $this->assertNotNull($dto->createdAt);
        $this->assertSame('2024-06-01', $dto->createdAt->toDateString());
    }
}
