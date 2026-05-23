<?php

namespace Tests\Unit\Services;

use App\Enums\SprintStatus;
use App\Models\Project;
use App\Models\Sprint;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Services\SprintService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SprintServiceTest extends TestCase
{
    private SprintRepositoryInterface|MockInterface $sprintRepo;
    private IssueRepositoryInterface|MockInterface  $issueRepo;
    private SprintService $service;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sprintRepo = Mockery::mock(SprintRepositoryInterface::class);
        $this->issueRepo  = Mockery::mock(IssueRepositoryInterface::class);
        $this->service    = new SprintService($this->sprintRepo, $this->issueRepo);
        $this->project    = (new Project())->forceFill(['id' => 1, 'name' => 'Test', 'key' => 'TST', 'description' => null]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_planned_and_active_filters_out_completed(): void
    {
        $planned   = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'planned']);
        $active    = (new Sprint())->forceFill(['id' => 2, 'name' => 'S2', 'goal' => null, 'status' => 'active']);
        $completed = (new Sprint())->forceFill(['id' => 3, 'name' => 'S3', 'goal' => null, 'status' => 'completed']);

        $this->sprintRepo->shouldReceive('forProject')
            ->once()
            ->with($this->project)
            ->andReturn(collect([$planned, $active, $completed]));

        $result = $this->service->getPlannedAndActive($this->project);

        $this->assertCount(2, $result);
        $this->assertSame(SprintStatus::Planned, $result[0]->status);
        $this->assertSame(SprintStatus::Active, $result[1]->status);
    }

    public function test_start_throws_422_when_sprint_is_not_planned(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'active']);

        $this->assertHttpException(422, fn () => $this->service->start($this->project, $sprint));
    }

    public function test_start_throws_422_when_project_already_has_active_sprint(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'planned']);

        $this->sprintRepo->shouldReceive('hasActive')
            ->once()
            ->with($this->project)
            ->andReturn(true);

        $this->assertHttpException(422, fn () => $this->service->start($this->project, $sprint));
    }

    public function test_start_updates_status_to_active(): void
    {
        $sprint  = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'planned']);
        $started = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'active']);

        $this->sprintRepo->shouldReceive('hasActive')->once()->andReturn(false);
        $this->sprintRepo->shouldReceive('update')
            ->once()
            ->with($sprint, ['status' => 'active'])
            ->andReturn($started);

        $dto = $this->service->start($this->project, $sprint);

        $this->assertSame(SprintStatus::Active, $dto->status);
    }

    public function test_complete_throws_422_when_sprint_is_not_active(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'planned']);

        $this->assertHttpException(422, fn () => $this->service->complete($this->project, $sprint));
    }

    public function test_complete_moves_incomplete_issues_and_marks_completed(): void
    {
        $sprint    = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'active']);
        $completed = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'completed']);

        $this->issueRepo->shouldReceive('moveIncompleteToBacklog')->once()->with($sprint);
        $this->sprintRepo->shouldReceive('update')
            ->once()
            ->with($sprint, ['status' => 'completed'])
            ->andReturn($completed);

        $dto = $this->service->complete($this->project, $sprint);

        $this->assertSame(SprintStatus::Completed, $dto->status);
    }

    public function test_delete_throws_422_when_sprint_is_active(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'active']);

        $this->assertHttpException(422, fn () => $this->service->delete($this->project, $sprint));
    }

    public function test_delete_moves_all_issues_to_backlog_then_deletes(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 1, 'name' => 'S1', 'goal' => null, 'status' => 'planned']);

        $this->issueRepo->shouldReceive('moveAllToBacklog')->once()->with($sprint);
        $this->sprintRepo->shouldReceive('delete')->once()->with($sprint);

        $this->service->delete($this->project, $sprint);
    }

    private function assertHttpException(int $status, callable $action): void
    {
        try {
            $action();
            $this->fail("Expected HTTP {$status} exception was not thrown.");
        } catch (HttpException $e) {
            $this->assertSame($status, $e->getStatusCode());
        }
    }
}
