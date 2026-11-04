<?php

namespace Plugins\TeamTracking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Enums\TeamStatus;
use Plugins\TeamTracking\Http\Requests\StoreTeamRequest;
use Plugins\TeamTracking\Http\Requests\UpdateTeamRequest;
use Plugins\TeamTracking\Models\Team;
use Plugins\TeamTracking\Models\TeamCheckIn;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $query = Team::query()
            ->with(['leader', 'latestCheckIn'])
            ->withCount('members');

        if ($search !== '') {
            $query->matching($search);
        }

        if (in_array($status, TeamStatus::values(), true)) {
            $query->where('status', $status);
        }

        return view('team-tracking::index', [
            'teams' => $query->orderBy('name')->paginate((int) config('team-tracking.per_page', 15))->withQueryString(),
            'search' => $search,
            'status' => $status,
            'statuses' => TeamStatus::options(),
            'stats' => [
                'total' => Team::query()->count(),
                'active' => Team::query()->active()->count(),
                'members' => DB::table('team_user')->distinct()->count('user_id'),
                'attention' => TeamCheckIn::query()->where('status', CheckInStatus::Attention->value)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('team-tracking::create', [
            'users' => $this->users(),
            'statuses' => TeamStatus::options(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $team = Team::query()->create($request->validated());

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', 'Equipe criada com sucesso.');
    }

    public function show(Team $team): View
    {
        $team->load(['leader', 'members', 'checkIns.user', 'latestCheckIn']);

        return view('team-tracking::show', [
            'team' => $team,
            'checkInStatuses' => CheckInStatus::options(),
            'availableUsers' => $this->users()
                ->reject(fn (User $user): bool => $team->members->contains('id', $user->getKey()))
                ->values(),
        ]);
    }

    public function edit(Team $team): View
    {
        return view('team-tracking::edit', [
            'team' => $team,
            'users' => $this->users(),
            'statuses' => TeamStatus::options(),
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->validated());

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('status', 'Equipe atualizada com sucesso.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()
            ->route('admin.teams.index')
            ->with('status', 'Equipe excluída com sucesso.');
    }

    /**
     * @return Collection<int, User>
     */
    private function users(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->orderBy('surname')
            ->get(['id', 'name', 'surname']);
    }
}
