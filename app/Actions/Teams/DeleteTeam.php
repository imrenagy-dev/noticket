<?php

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTeam
{
    public function handle(User $actor, Team $team): void
    {
        DB::transaction(function () use ($actor, $team) {
            User::where('current_team_id', $team->id)
                ->where('id', '!=', $actor->id)
                ->each(fn (User $u) => $u->switchTeam($u->personalTeam()));

            $team->invitations()->delete();
            $team->memberships()->delete();
            $team->delete();
        });
    }
}
