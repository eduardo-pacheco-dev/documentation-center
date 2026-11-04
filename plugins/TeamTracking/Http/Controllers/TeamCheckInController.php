<?php

namespace Plugins\TeamTracking\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Http\Requests\StoreTeamCheckInRequest;
use Plugins\TeamTracking\Http\Requests\UpdateTeamCheckInRequest;
use Plugins\TeamTracking\Models\Team;
use Plugins\TeamTracking\Models\TeamCheckIn;

class TeamCheckInController extends Controller
{
    public function store(StoreTeamCheckInRequest $request, Team $team): RedirectResponse
    {
        $team->checkIns()->create([
            ...$request->validated(),
            'user_id' => $request->user()->getKey(),
        ]);

        return back()->with('status', 'Acompanhamento registrado com sucesso.');
    }

    public function edit(Team $team, TeamCheckIn $checkIn): View
    {
        abort_unless($checkIn->team_id === $team->getKey(), 404);

        return view('team-tracking::check-ins.edit', [
            'team' => $team,
            'checkIn' => $checkIn,
            'statuses' => CheckInStatus::options(),
        ]);
    }

    public function update(UpdateTeamCheckInRequest $request, Team $team, TeamCheckIn $checkIn): RedirectResponse
    {
        abort_unless($checkIn->team_id === $team->getKey(), 404);

        $checkIn->update($request->validated());

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', 'Acompanhamento atualizado com sucesso.');
    }

    public function destroy(Team $team, TeamCheckIn $checkIn): RedirectResponse
    {
        abort_unless($checkIn->team_id === $team->getKey(), 404);

        $checkIn->delete();

        return back()->with('status', 'Acompanhamento removido com sucesso.');
    }
}
