<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Collection;

interface ProjectRepositoryInterface
{
    public function forTeam(Team $team): Collection;

    public function create(Team $team, array $data, int $creatorId): Project;

    public function update(Project $project, array $data): Project;

    public function delete(Project $project): void;
}
