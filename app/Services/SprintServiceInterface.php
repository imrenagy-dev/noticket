<?php

namespace App\Services;

use App\Data\SprintDTO;
use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

interface SprintServiceInterface
{
    public function getPlannedAndActive(Project $project): Collection;

    public function create(Project $project, ?string $name = null, ?string $goal = null): SprintDTO;

    public function update(Sprint $sprint, array $data): SprintDTO;

    public function start(Project $project, Sprint $sprint): SprintDTO;

    public function complete(Project $project, Sprint $sprint): SprintDTO;

    public function delete(Project $project, Sprint $sprint): void;
}
