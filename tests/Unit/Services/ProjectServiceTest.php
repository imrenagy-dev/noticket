<?php

namespace Tests\Unit\Services;

use App\Data\ProjectDTO;
use App\Models\Project;
use App\Models\Team;
use App\Repositories\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    private ProjectRepositoryInterface|MockInterface $repo;
    private ProjectService $service;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = Mockery::mock(ProjectRepositoryInterface::class);
        $this->service = new ProjectService($this->repo);
        $this->team    = (new Team())->forceFill(['id' => 1, 'name' => 'Team A', 'slug' => 'team-a']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_for_team_maps_collection_to_dtos(): void
    {
        $p1 = (new Project())->forceFill(['id' => 1, 'name' => 'P1', 'key' => 'P1', 'description' => null]);
        $p2 = (new Project())->forceFill(['id' => 2, 'name' => 'P2', 'key' => 'P2', 'description' => null]);

        $this->repo->shouldReceive('forTeam')
            ->once()
            ->with($this->team)
            ->andReturn(collect([$p1, $p2]));

        $result = $this->service->listForTeam($this->team);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ProjectDTO::class, $result->all());
        $this->assertSame('P1', $result[0]->name);
        $this->assertSame('P2', $result[1]->name);
    }

    public function test_create_returns_dto(): void
    {
        $project = (new Project())->forceFill(['id' => 3, 'name' => 'New', 'key' => 'NEW', 'description' => null]);

        $this->repo->shouldReceive('create')
            ->once()
            ->with($this->team, ['name' => 'New', 'key' => 'NEW'], 1)
            ->andReturn($project);

        $dto = $this->service->create($this->team, ['name' => 'New', 'key' => 'NEW'], 1);

        $this->assertInstanceOf(ProjectDTO::class, $dto);
        $this->assertSame('New', $dto->name);
        $this->assertSame('NEW', $dto->key);
    }

    public function test_update_returns_dto_with_new_values(): void
    {
        $original = (new Project())->forceFill(['id' => 3, 'name' => 'Old', 'key' => 'OLD', 'description' => null]);
        $updated  = (new Project())->forceFill(['id' => 3, 'name' => 'New Name', 'key' => 'OLD', 'description' => null]);

        $this->repo->shouldReceive('update')
            ->once()
            ->with($original, ['name' => 'New Name'])
            ->andReturn($updated);

        $dto = $this->service->update($original, ['name' => 'New Name']);

        $this->assertSame('New Name', $dto->name);
    }

    public function test_delete_delegates_to_repo(): void
    {
        $project = (new Project())->forceFill(['id' => 3, 'name' => 'Old', 'key' => 'OLD', 'description' => null]);

        $this->repo->shouldReceive('delete')->once()->with($project);

        $this->service->delete($project);
    }
}
