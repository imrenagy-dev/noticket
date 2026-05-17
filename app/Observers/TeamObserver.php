<?php

namespace App\Observers;

use App\Contracts\TeamSlugGeneratorContract;
use App\Models\Team;

class TeamObserver
{
    public function __construct(private TeamSlugGeneratorContract $slugGenerator) {}

    public function creating(Team $team): void
    {
        if (empty($team->slug)) {
            $team->slug = $this->slugGenerator->generate($team->name);
        }
    }

    public function updating(Team $team): void
    {
        if ($team->isDirty('name')) {
            $team->slug = $this->slugGenerator->generate($team->name, $team->id);
        }
    }
}
