<?php

namespace App\Repositories;

use App\Models\Team;
use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function getStats(Team $team, int $userId): array;

    public function getMyIssues(Team $team, int $userId, int $limit = 10): Collection;

    public function getRecentIssues(Team $team, int $limit = 10): Collection;
}
