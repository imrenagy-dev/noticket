<?php

namespace App\Services;

use App\Repositories\SprintRepositoryInterface;
use App\Models\Project;
use App\Models\Sprint;

class SprintService
{
    public function __construct(private SprintRepositoryInterface $sprints) {}

    public function create(Project $project, ?string $name = null, ?string $goal = null): Sprint
    {
        $count = $this->sprints->countForProject($project);
        $name  = $name ?? $project->key . ' Sprint ' . ($count + 1);

        return $this->sprints->create($project, $name, $goal);
    }

    public function update(Sprint $sprint, array $data): Sprint
    {
        return $this->sprints->update($sprint, $data);
    }

    public function start(Project $project, Sprint $sprint): Sprint
    {
        abort_if($sprint->status !== 'planned', 422);
        abort_if($this->sprints->hasActive($project), 422, 'A sprint is already active.');

        return $this->sprints->update($sprint, ['status' => 'active']);
    }

    public function complete(Project $project, Sprint $sprint): Sprint
    {
        abort_if($sprint->status !== 'active', 422);

        $project->issues()
            ->where('sprint_id', $sprint->id)
            ->where('status', '!=', 'done')
            ->update(['sprint_id' => null]);

        return $this->sprints->update($sprint, ['status' => 'completed']);
    }

    public function delete(Project $project, Sprint $sprint): void
    {
        abort_if($sprint->status === 'active', 422, 'Cannot delete an active sprint.');

        $project->issues()
            ->where('sprint_id', $sprint->id)
            ->update(['sprint_id' => null]);

        $this->sprints->delete($sprint);
    }
}
