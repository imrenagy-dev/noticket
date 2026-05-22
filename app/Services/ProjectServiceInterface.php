<?php

namespace App\Services;

use App\Data\ProjectDTO;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Collection;

interface ProjectServiceInterface
{
    public function listForTeam(Team $team): Collection;

    public function create(Team $team, array $data, int $creatorId): ProjectDTO;

    public function update(Project $project, array $data): ProjectDTO;

    public function delete(Project $project): void;
}
