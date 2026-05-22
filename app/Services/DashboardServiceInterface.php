<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Collection;

interface DashboardServiceInterface
{
    public function getStats(Team $team, int $userId): array;

    public function getMyIssues(Team $team, int $userId): Collection;

    public function getRecentIssues(Team $team): Collection;
}
