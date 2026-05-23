<?php

namespace Tests\Unit\Services;

use App\Data\IssueDTO;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Team;
use App\Repositories\DashboardRepositoryInterface;
use App\Services\DashboardService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    private DashboardRepositoryInterface|MockInterface $repo;
    private DashboardService $service;
    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo    = Mockery::mock(DashboardRepositoryInterface::class);
        $this->service = new DashboardService($this->repo);
        $this->team    = (new Team())->forceFill(['id' => 1, 'name' => 'Team A', 'slug' => 'team-a']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_stats_delegates_to_repo(): void
    {
        $expected = ['total' => 10, 'done' => 4];

        $this->repo->shouldReceive('getStats')
            ->once()
            ->with($this->team, 1)
            ->andReturn($expected);

        $this->assertSame($expected, $this->service->getStats($this->team, 1));
    }

    public function test_get_my_issues_maps_to_issue_dtos(): void
    {
        $issue = $this->makeIssue();

        $this->repo->shouldReceive('getMyIssues')
            ->once()
            ->with($this->team, 1)
            ->andReturn(collect([$issue]));

        $result = $this->service->getMyIssues($this->team, 1);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(IssueDTO::class, $result[0]);
        $this->assertSame('TST-1', $result[0]->issueKey);
    }

    public function test_get_recent_issues_maps_to_issue_dtos(): void
    {
        $issue = $this->makeIssue();

        $this->repo->shouldReceive('getRecentIssues')
            ->once()
            ->with($this->team)
            ->andReturn(collect([$issue]));

        $result = $this->service->getRecentIssues($this->team);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(IssueDTO::class, $result[0]);
        $this->assertSame('TST-1', $result[0]->issueKey);
    }

    private function makeIssue(): Issue
    {
        $project = (new Project())->forceFill([
            'id'          => 1,
            'name'        => 'Test',
            'key'         => 'TST',
            'description' => null,
        ]);

        $issue = (new Issue())->forceFill([
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
        ]);
        $issue->setRelation('project', $project);

        return $issue;
    }
}
