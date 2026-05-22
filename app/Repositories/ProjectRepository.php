<?php

namespace App\Repositories;

use App\Repositories\ProjectRepositoryInterface;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function forTeam(Team $team): Collection
    {
        return $team->projects()
            ->withCount('issues')
            ->orderBy('name')
            ->get();
    }

    public function create(Team $team, array $data, int $creatorId): Project
    {
        return $team->projects()->create([
            ...$data,
            'created_by' => $creatorId,
        ]);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
