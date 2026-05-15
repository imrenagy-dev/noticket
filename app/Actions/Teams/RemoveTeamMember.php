<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;

class RemoveTeamMember
{
    public function handle(Team $team, User $user): void
    {
        $team->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($user->isCurrentTeam($team)) {
            $user->switchTeam($user->personalTeam());
        }
    }
}
