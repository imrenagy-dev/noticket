<?php

namespace App\Repositories;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

interface IssueRepositoryInterface
{
    public function search(Project $project, string $query, int $limit = 30): Collection;

    public function forBacklog(Project $project): Collection;

    public function forSprint(Sprint $sprint): Collection;

    /** Returns issues for the sprint grouped by status key. */
    public function boardColumns(Sprint $sprint): array;

    public function create(Project $project, array $data): Issue;

    public function update(Issue $issue, array $data): Issue;

    public function delete(Issue $issue): void;

    public function bulkUpdateSprint(Project $project, array $issueIds, ?int $sprintId): void;

    public function moveIncompleteToBacklog(Sprint $sprint): void;

    public function moveAllToBacklog(Sprint $sprint): void;

    public function loadDetail(Issue $issue): Issue;
}
