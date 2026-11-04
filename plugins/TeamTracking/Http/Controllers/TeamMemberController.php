<?php

namespace Plugins\TeamTracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Plugins\TeamTracking\Http\Requests\StoreTeamMemberRequest;
use Plugins\TeamTracking\Models\Team;

class TeamMemberController extends Controller
{
    public function store(StoreTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $team->members()->attach($request->validated('user_id'), [
            'role' => $request->validated('role'),
            'joined_at' => $request->validated('joined_at') ?? today()->toDateString(),
        ]);

        return back()->with('status', 'Membro adicionado à equipe.');
    }

    public function destroy(Team $team, User $user): RedirectResponse
    {
        $team->members()->detach($user->getKey());

        return back()->with('status', 'Membro removido da equipe.');
    }
}
