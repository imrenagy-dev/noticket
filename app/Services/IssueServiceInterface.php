<?php

namespace App\Services;

use App\Data\IssueDTO;
use App\Data\IssueDetailDTO;
use App\Data\IssueSearchDTO;
use App\Models\Issue;
use App\Models\Project;
use Illuminate\Support\Collection;

interface IssueServiceInterface
{
    public function search(Project $project, string $query, int $limit = 30): Collection;

    public function loadDetail(Issue $issue, string $projectKey): IssueDetailDTO;

    public function create(Project $project, array $data, int $reporterId): IssueDTO;

    public function update(Issue $issue, Project $project, array $data, int $userId): IssueDTO;

    public function delete(Issue $issue, int $userId): void;

    public function bulkUpdateSprint(Project $project, array $issueIds, ?int $sprintId): void;
}
