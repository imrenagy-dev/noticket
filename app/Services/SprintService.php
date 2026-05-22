<?php

namespace App\Services;

use App\Data\SprintDTO;
use App\Repositories\IssueRepositoryInterface;
use App\Repositories\SprintRepositoryInterface;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

class SprintService implements SprintServiceInterface
{
    public function __construct(
        private SprintRepositoryInterface $sprints,
        private IssueRepositoryInterface  $issues,
    ) {}

    public function getPlannedAndActive(Project $project): Collection
    {
        return $this->sprints->forProject($project)
            ->filter(fn (Sprint $s) => in_array($s->status, ['planned', 'active']))
            ->map(fn (Sprint $s) => SprintDTO::fromModel($s))
            ->values();
    }

    public function create(Project $project, ?string $name = null, ?string $goal = null): SprintDTO
    {
        $count = $this->sprints->countForProject($project);
        $name  = $name ?? $project->key . ' Sprint ' . ($count + 1);

        return SprintDTO::fromModel($this->sprints->create($project, $name, $goal));
    }

    public function update(Sprint $sprint, array $data): SprintDTO
    {
        return SprintDTO::fromModel($this->sprints->update($sprint, $data));
    }

    public function start(Project $project, Sprint $sprint): SprintDTO
    {
        abort_if($sprint->status !== 'planned', 422);
        abort_if($this->sprints->hasActive($project), 422, 'A sprint is already active.');

        return SprintDTO::fromModel($this->sprints->update($sprint, ['status' => 'active']));
    }

    public function complete(Project $project, Sprint $sprint): SprintDTO
    {
        abort_if($sprint->status !== 'active', 422);

        $this->issues->moveIncompleteToBacklog($sprint);

        return SprintDTO::fromModel($this->sprints->update($sprint, ['status' => 'completed']));
    }

    public function delete(Project $project, Sprint $sprint): void
    {
        abort_if($sprint->status === 'active', 422, 'Cannot delete an active sprint.');

        $this->issues->moveAllToBacklog($sprint);

        $this->sprints->delete($sprint);
    }
}
