<?php

namespace App\Services;

use App\Data\ProjectDTO;
use App\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Collection;

class ProjectService implements ProjectServiceInterface
{
    public function __construct(private ProjectRepositoryInterface $projects) {}

    public function listForTeam(Team $team): Collection
    {
        return $this->projects->forTeam($team)
            ->map(fn (Project $p) => ProjectDTO::fromModel($p));
    }

    public function create(Team $team, array $data, int $creatorId): ProjectDTO
    {
        return ProjectDTO::fromModel($this->projects->create($team, $data, $creatorId));
    }

    public function update(Project $project, array $data): ProjectDTO
    {
        return ProjectDTO::fromModel($this->projects->update($project, $data));
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }
}
