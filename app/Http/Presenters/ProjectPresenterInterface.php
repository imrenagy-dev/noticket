<?php

namespace App\Http\Presenters;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Team;

interface ProjectPresenterInterface
{
    public function project(Project $project): array;

    public function sprint(Sprint $sprint): array;

    public function issue(Issue $issue, string $projectKey): array;

    public function members(Team $team): array;
}
