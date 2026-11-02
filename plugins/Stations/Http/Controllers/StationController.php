<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Plugins\Stations\Enums\StationLinkStatus;
use Plugins\Stations\Enums\StationOperator;
use Plugins\Stations\Enums\StationStatus;
use Plugins\Stations\Enums\StationTechnology;
use Plugins\Stations\Http\Requests\StoreStationRequest;
use Plugins\Stations\Http\Requests\UpdateStationRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationLink;
use Plugins\Stations\Support\BrazilianStates;

class StationController extends Controller
{
    /**
     * List the stations owned by the authenticated user.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Station::class);

        $search = trim((string) $request->query('search', ''));

        $status = $request->query('status');
        $status = in_array($status, array_column(StationStatus::cases(), 'value'), true) ? $status : null;

        $sortable = ['code', 'name', 'site_id', 'operator', 'technology', 'city', 'state', 'status', 'updated_at'];
        $sort = in_array($request->query('sort'), $sortable, true) ? (string) $request->query('sort') : 'updated_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->query('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 36], true) ? $perPage : 12;

        $view = in_array($request->query('view'), ['table', 'cards'], true) ? (string) $request->query('view') : 'table';

        $stations = Station::query()
            ->ownedBy($request->user())
            ->withCount(['documents', 'comments', 'linksAsEndA', 'linksAsEndB'])
            ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search) {
                $inner->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('site_id', 'like', "%{$search}%")
                    ->orWhere('end_id', 'like', "%{$search}%")
                    ->orWhere('operator', 'like', "%{$search}%")
                    ->orWhere('technology', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            }))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('stations::index', [
            'stations' => $stations,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
            'statuses' => StationStatus::cases(),
            'stats' => [
                'total' => Station::query()->ownedBy($request->user())->count(),
                'active' => Station::query()->ownedBy($request->user())->where('status', StationStatus::Active)->count(),
                'maintenance' => Station::query()->ownedBy($request->user())->where('status', StationStatus::Maintenance)->count(),
                'links' => StationLink::query()->ownedBy($request->user())->count(),
            ],
        ]);
    }

    /**
     * Show the station creation form.
     */
    public function create(): View
    {
        $this->authorize('create', Station::class);

        return view('stations::create', $this->formData(new Station));
    }

    /**
     * Store a new station.
     */
    public function store(StoreStationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $station = Station::query()->create([
            ...$data,
            'user_id' => $request->user()->getKey(),
            'status' => $data['status'] ?? StationStatus::Active->value,
        ]);

        return redirect()
            ->route('stations.show', $station)
            ->with('status', 'Station criada com sucesso.');
    }

    /**
     * Show a single station.
     */
    public function show(Request $request, Station $station): View
    {
        $this->authorize('view', $station);

        $station->load([
            'documents',
            'comments.user',
            'linksAsEndA' => fn ($query) => $query->with('stationB:id,code,name')->orderByDesc('updated_at'),
            'linksAsEndB' => fn ($query) => $query->with('stationA:id,code,name')->orderByDesc('updated_at'),
        ]);

        $stationOptions = Station::query()
            ->ownedBy($request->user())
            ->whereKeyNot($station->getKey())
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return view('stations::show', [
            'station' => $station,
            'stationOptions' => $stationOptions,
            'linkStatuses' => StationLinkStatus::cases(),
        ]);
    }

    /**
     * Show the station edit form.
     */
    public function edit(Station $station): View
    {
        $this->authorize('update', $station);

        return view('stations::edit', $this->formData($station));
    }

    /**
     * Update the given station.
     */
    public function update(UpdateStationRequest $request, Station $station): RedirectResponse
    {
        $station->update($request->validated());

        return redirect()
            ->route('stations.show', $station)
            ->with('status', 'Station atualizada com sucesso.');
    }

    /**
     * Soft delete the given station.
     */
    public function destroy(Station $station): RedirectResponse
    {
        $this->authorize('delete', $station);

        $station->delete();

        return redirect()
            ->route('stations.index')
            ->with('status', 'Station removida com sucesso.');
    }

    /**
     * Data shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(Station $station): array
    {
        return [
            'station' => $station,
            'statuses' => StationStatus::cases(),
            'operators' => StationOperator::cases(),
            'technologies' => StationTechnology::cases(),
            'states' => BrazilianStates::codes(),
        ];
    }
}
