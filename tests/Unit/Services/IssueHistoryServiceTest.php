<?php

namespace Tests\Unit\Services;

use App\Models\Issue;
use App\Repositories\IssueHistoryRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\IssueHistoryService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class IssueHistoryServiceTest extends TestCase
{
    private IssueHistoryRepositoryInterface|MockInterface $historyRepo;
    private UserRepositoryInterface|MockInterface         $users;
    private SprintRepositoryInterface|MockInterface       $sprints;
    private IssueHistoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->historyRepo = Mockery::mock(IssueHistoryRepositoryInterface::class);
        $this->users       = Mockery::mock(UserRepositoryInterface::class);
        $this->sprints     = Mockery::mock(SprintRepositoryInterface::class);
        $this->service     = new IssueHistoryService($this->historyRepo, $this->users, $this->sprints);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_created_writes_created_action(): void
    {
        $issue = $this->makeIssue();

        $this->historyRepo->shouldReceive('record')
            ->once()
            ->with($issue, Mockery::on(fn (array $d) => $d['user_id'] === 1 && $d['action'] === 'created'));

        $this->service->recordCreated($issue, 1);
    }

    public function test_record_deleted_writes_deleted_action(): void
    {
        $issue = $this->makeIssue();

        $this->historyRepo->shouldReceive('record')
            ->once()
            ->with($issue, Mockery::on(fn (array $d) => $d['user_id'] === 2 && $d['action'] === 'deleted'));

        $this->service->recordDeleted($issue, 2);
    }

    public function test_compute_update_entries_detects_title_change(): void
    {
        $issue = $this->makeIssue(['title' => 'Old title']);

        $entries = $this->service->computeUpdateEntries($issue, ['title' => 'New title']);

        $this->assertCount(1, $entries);
        $this->assertSame('updated', $entries[0]['action']);
        $this->assertSame('title', $entries[0]['field']);
        $this->assertSame('Old title', $entries[0]['old_value']);
        $this->assertSame('New title', $entries[0]['new_value']);
    }

    public function test_compute_update_entries_skips_unchanged_title(): void
    {
        $issue = $this->makeIssue(['title' => 'Same title']);

        $entries = $this->service->computeUpdateEntries($issue, ['title' => 'Same title']);

        $this->assertEmpty($entries);
    }

    public function test_compute_update_entries_resolves_status_enum_to_label(): void
    {
        $issue = $this->makeIssue(['status' => 'todo']);

        $entries = $this->service->computeUpdateEntries($issue, ['status' => 'in_progress']);

        $this->assertCount(1, $entries);
        $this->assertSame('status', $entries[0]['field']);
        $this->assertSame('To Do', $entries[0]['old_value']);
        $this->assertSame('In Progress', $entries[0]['new_value']);
    }

    public function test_compute_update_entries_resolves_assignee_name(): void
    {
        $issue = $this->makeIssue(['assignee_id' => null]);

        $this->users->shouldReceive('findNameById')->with(7)->andReturn('Carol');

        $entries = $this->service->computeUpdateEntries($issue, ['assignee_id' => 7]);

        $this->assertCount(1, $entries);
        $this->assertSame('assignee', $entries[0]['field']);
        $this->assertNull($entries[0]['old_value']);
        $this->assertSame('Carol', $entries[0]['new_value']);
    }

    public function test_compute_update_entries_resolves_sprint_name(): void
    {
        $issue = $this->makeIssue(['sprint_id' => null]);

        $this->sprints->shouldReceive('findNameById')->with(3)->andReturn('Sprint 3');

        $entries = $this->service->computeUpdateEntries($issue, ['sprint_id' => 3]);

        $this->assertCount(1, $entries);
        $this->assertSame('sprint', $entries[0]['field']);
        $this->assertNull($entries[0]['old_value']);
        $this->assertSame('Sprint 3', $entries[0]['new_value']);
    }

    public function test_persist_update_entries_records_each_entry_with_user_id(): void
    {
        $issue   = $this->makeIssue();
        $entries = [
            ['action' => 'updated', 'field' => 'title', 'old_value' => 'Old', 'new_value' => 'New'],
            ['action' => 'updated', 'field' => 'status', 'old_value' => 'To Do', 'new_value' => 'Done'],
        ];

        $this->historyRepo->shouldReceive('record')->twice();

        $this->service->persistUpdateEntries($issue, $entries, userId: 1);
    }

    private function makeIssue(array $overrides = []): Issue
    {
        $issue = new Issue();
        // setRawAttributes with sync=true populates $original so getRawOriginal() works
        $issue->setRawAttributes(array_merge([
            'id'          => 1,
            'type'        => 'task',
            'status'      => 'todo',
            'priority'    => 'medium',
            'title'       => 'Test issue',
            'description' => null,
            'assignee_id' => null,
            'sprint_id'   => null,
        ], $overrides), true);

        return $issue;
    }
}
