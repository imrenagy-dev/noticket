<?php

namespace App\Http\Controllers\Teams;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\DeleteTeam;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\DeleteTeamRequest;
use App\Http\Requests\Teams\SaveTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('teams/index', [
            'teams' => $request->user()->toUserTeams(includeCurrent: true),
        ]);
    }

    public function store(SaveTeamRequest $request, CreateTeam $createTeam): RedirectResponse
    {
        $team = $createTeam->handle($request->user(), $request->validated('name'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team created.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function edit(Request $request, Team $team): Response
    {
        $user = $request->user();

        return Inertia::render('teams/edit', [
            'team' => [
                'id'         => $team->id,
                'name'       => $team->name,
                'slug'       => $team->slug,
                'isPersonal' => $team->is_personal,
            ],
            'members' => $team->members()->get()->map(fn ($member) => [
                'id'         => $member->id,
                'name'       => $member->name,
                'email'      => $member->email,
                'avatar'     => $member->avatar ?? null,
                'role'       => $member->pivot->role->value,
                'role_label' => $member->pivot->role?->label(),
            ]),
            'invitations' => $team->invitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn ($invitation) => [
                    'code'       => $invitation->code,
                    'email'      => $invitation->email,
                    'role'       => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'created_at' => $invitation->created_at->toISOString(),
                ]),
            'permissions'    => $user->toTeamPermissions($team),
            'availableRoles' => TeamRole::assignable(),
        ]);
    }

    public function update(SaveTeamRequest $request, Team $team): RedirectResponse
    {
        Gate::authorize('update', $team);

        $team = DB::transaction(function () use ($request, $team) {
            $team = Team::whereKey($team->id)->lockForUpdate()->firstOrFail();
            $team->update(['name' => $request->validated('name')]);
            return $team;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team updated.')]);

        return to_route('teams.edit', ['team' => $team->slug]);
    }

    public function switch(Request $request, Team $team): RedirectResponse
    {
        abort_unless($request->user()->belongsToTeam($team), 403);

        $request->user()->switchTeam($team);

        return back();
    }

    public function destroy(DeleteTeamRequest $request, Team $team, DeleteTeam $deleteTeam): RedirectResponse
    {
        $user         = $request->user();
        $fallbackTeam = $user->isCurrentTeam($team) ? $user->fallbackTeam($team) : null;

        $deleteTeam->handle($user, $team);

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Team deleted.')]);

        return to_route('teams.index');
    }
}
