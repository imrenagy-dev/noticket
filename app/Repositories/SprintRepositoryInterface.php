<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Collection;

interface SprintRepositoryInterface
{
    public function forProject(Project $project): Collection;

    public function hasActive(Project $project): bool;

    public function findActive(Project $project): ?Sprint;

    public function countForProject(Project $project): int;

    public function create(Project $project, string $name, ?string $goal = null): Sprint;

    public function update(Sprint $sprint, array $data): Sprint;

    public function delete(Sprint $sprint): void;

    public function findById(int $id): ?Sprint;

    public function findNameById(int $id): ?string;
}
