<?php

namespace App\Repositories;

use App\Repositories\SprintRepositoryInterface;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

class SprintRepository implements SprintRepositoryInterface
{
    public function forProject(Project $project): Collection
    {
        return $project->sprints()
            ->orderByRaw("FIELD(status, 'active', 'planned', 'completed')")
            ->orderBy('created_at')
            ->get();
    }

    public function hasActive(Project $project): bool
    {
        return $project->sprints()->where('status', 'active')->exists();
    }

    public function findActive(Project $project): ?Sprint
    {
        return $project->sprints()->where('status', 'active')->first();
    }

    public function countForProject(Project $project): int
    {
        return $project->sprints()->count();
    }

    public function create(Project $project, string $name, ?string $goal = null): Sprint
    {
        return $project->sprints()->create([
            'name'   => $name,
            'goal'   => $goal,
            'status' => 'planned',
        ]);
    }

    public function update(Sprint $sprint, array $data): Sprint
    {
        $sprint->update($data);

        return $sprint->fresh();
    }

    public function delete(Sprint $sprint): void
    {
        $sprint->delete();
    }

    public function findById(int $id): ?Sprint
    {
        return Sprint::find($id);
    }

    public function findNameById(int $id): ?string
    {
        return Sprint::find($id)?->name;
    }
}
