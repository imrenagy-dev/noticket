<?php

namespace App\Services;

use App\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Collection;

class ProjectService
{
    public function __construct(private ProjectRepositoryInterface $projects) {}

    public function listForTeam(Team $team): Collection
    {
        return $this->projects->forTeam($team);
    }

    public function create(Team $team, array $data, int $creatorId): Project
    {
        return $this->projects->create($team, $data, $creatorId);
    }

    public function update(Project $project, array $data): Project
    {
        return $this->projects->update($project, $data);
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }
}
