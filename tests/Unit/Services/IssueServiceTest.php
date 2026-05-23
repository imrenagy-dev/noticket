<?php

namespace Tests\Unit\Services;

use App\Data\IssueDTO;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Services\IssueHistoryServiceInterface;
use App\Services\IssueService;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IssueServiceTest extends TestCase
{
    private IssueRepositoryInterface|MockInterface     $issueRepo;
    private IssueHistoryServiceInterface|MockInterface $history;
    private SprintRepositoryInterface|MockInterface    $sprintRepo;
    private IssueService $service;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->issueRepo  = Mockery::mock(IssueRepositoryInterface::class);
        $this->history    = Mockery::mock(IssueHistoryServiceInterface::class);
        $this->sprintRepo = Mockery::mock(SprintRepositoryInterface::class);
        $this->service    = new IssueService($this->issueRepo, $this->history, $this->sprintRepo);
        $this->project    = (new Project())->forceFill(['id' => 1, 'name' => 'Test', 'key' => 'TST', 'description' => null]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_aborts_422_when_sprint_belongs_to_different_project(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 5, 'project_id' => 99, 'name' => 'S', 'status' => 'planned', 'goal' => null]);

        $this->sprintRepo->shouldReceive('findById')->with(5)->andReturn($sprint);

        $this->assertHttpException(422, fn () => $this->service->create(
            $this->project,
            ['sprint_id' => 5, 'title' => 'T', 'type' => 'task', 'status' => 'todo', 'priority' => 'medium'],
            reporterId: 1
        ));
    }

    public function test_create_records_issue_and_history(): void
    {
        $issue = $this->makeIssue();

        $this->issueRepo->shouldReceive('create')->once()->andReturn($issue);
        $this->history->shouldReceive('recordCreated')->once()->with($issue, 1);

        $dto = $this->service->create($this->project, [
            'title'    => 'Test issue',
            'type'     => 'task',
            'status'   => 'todo',
            'priority' => 'medium',
        ], reporterId: 1);

        $this->assertInstanceOf(IssueDTO::class, $dto);
        $this->assertSame(1, $dto->id);
    }

    public function test_update_aborts_422_when_sprint_belongs_to_different_project(): void
    {
        $issue  = $this->makeIssue();
        $sprint = (new Sprint())->forceFill(['id' => 5, 'project_id' => 99, 'name' => 'S', 'status' => 'planned', 'goal' => null]);

        $this->sprintRepo->shouldReceive('findById')->with(5)->andReturn($sprint);

        $this->assertHttpException(422, fn () => $this->service->update(
            $issue,
            $this->project,
            ['sprint_id' => 5, 'title' => 'New'],
            userId: 1
        ));
    }

    public function test_update_persists_history_entries_and_returns_dto(): void
    {
        $original = $this->makeIssue(['title' => 'Original']);
        $updated  = $this->makeIssue(['title' => 'Updated']);

        $entries = [['action' => 'updated', 'field' => 'title', 'old_value' => 'Original', 'new_value' => 'Updated']];

        $this->history->shouldReceive('computeUpdateEntries')
            ->once()
            ->with($original, ['title' => 'Updated'])
            ->andReturn($entries);
        $this->issueRepo->shouldReceive('update')
            ->once()
            ->with($original, ['title' => 'Updated'])
            ->andReturn($updated);
        $this->history->shouldReceive('persistUpdateEntries')
            ->once()
            ->with($updated, $entries, 1);

        $dto = $this->service->update($original, $this->project, ['title' => 'Updated'], userId: 1);

        $this->assertSame('Updated', $dto->title);
    }

    public function test_delete_records_history_then_deletes_issue(): void
    {
        $issue = $this->makeIssue();

        $this->history->shouldReceive('recordDeleted')->once()->with($issue, 1);
        $this->issueRepo->shouldReceive('delete')->once()->with($issue);

        $this->service->delete($issue, userId: 1);
    }

    public function test_bulk_update_sprint_aborts_422_when_sprint_belongs_to_different_project(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 5, 'project_id' => 99, 'name' => 'S', 'status' => 'planned', 'goal' => null]);

        $this->sprintRepo->shouldReceive('findById')->with(5)->andReturn($sprint);

        $this->assertHttpException(422, fn () => $this->service->bulkUpdateSprint(
            $this->project, [1, 2], sprintId: 5
        ));
    }

    public function test_bulk_update_sprint_delegates_to_repo_when_sprint_matches_project(): void
    {
        $sprint = (new Sprint())->forceFill(['id' => 5, 'project_id' => 1, 'name' => 'S', 'status' => 'planned', 'goal' => null]);

        $this->sprintRepo->shouldReceive('findById')->with(5)->andReturn($sprint);
        $this->issueRepo->shouldReceive('bulkUpdateSprint')
            ->once()
            ->with($this->project, [1, 2], 5);

        $this->service->bulkUpdateSprint($this->project, [1, 2], sprintId: 5);
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

    private function makeIssue(array $overrides = []): Issue
    {
        return (new Issue())->forceFill(array_merge([
            'id'           => 1,
            'number'       => 1,
            'project_id'   => 1,
            'type'         => 'task',
            'status'       => 'todo',
            'priority'     => 'medium',
            'title'        => 'Test issue',
            'description'  => null,
            'reporter_id'  => null,
            'assignee_id'  => null,
            'sprint_id'    => null,
            'board_order'  => 0,
            'backlog_order' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides));
    }
}
