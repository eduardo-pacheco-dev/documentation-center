<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Plugins\Stations\Http\Requests\StoreStationRequest;
use Plugins\Stations\Http\Requests\UpdateStationRequest;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;

class StationController extends Controller
{
    /**
     * List the registered stations.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $tecnologia = $request->string('tecnologia')->trim()->toString();

        $query = Station::query();

        if ($search !== '') {
            $query->matching($search);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($tecnologia !== '') {
            $query->where('tecnologia', $tecnologia);
        }

        $stations = $query
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $editStation = old('modal') === 'edit' && old('editing_id')
            ? Station::query()->find(old('editing_id'))
            : null;

        return view('stations::index', [
            'stations' => $stations,
            'editStation' => $editStation,
            'search' => $search,
            'status' => $status,
            'tecnologia' => $tecnologia,
            'statuses' => $this->distinctValues('status'),
            'tecnologias' => $this->distinctValues('tecnologia'),
            'stats' => [
                'total' => Station::query()->count(),
                'sites' => Station::query()->distinct()->count('station_id'),
                'municipios' => Station::query()->distinct()->count('municipio'),
            ],
        ]);
    }

    /**
     * Show the form to create a station.
     */
    public function create(): View
    {
        return view('stations::create');
    }

    /**
     * Store a newly created station.
     */
    public function store(StoreStationRequest $request): RedirectResponse
    {
        $station = Station::query()->create($request->validated());

        return redirect()
            ->route('admin.stations.show', $station)
            ->with('status', 'Estação cadastrada com sucesso.');
    }

    /**
     * Show the given station.
     */
    public function show(Station $station): View
    {
        $station->load([
            'attachments.uploader',
            'comments.user',
        ]);

        return view('stations::show', [
            'station' => $station,
            'ppiTssrFiles' => $station->attachments
                ->where('category', StationAttachment::CATEGORY_PPI_TSSR)
                ->values(),
            'attachmentFiles' => $station->attachments
                ->where('category', StationAttachment::CATEGORY_ATTACHMENT)
                ->values(),
            'comments' => $station->comments,
            'latitude' => is_numeric($station->latitude) ? (float) $station->latitude : null,
            'longitude' => is_numeric($station->longitude) ? (float) $station->longitude : null,
        ]);
    }

    /**
     * Show the form to edit the given station.
     */
    public function edit(Station $station): View
    {
        return view('stations::edit', [
            'station' => $station,
        ]);
    }

    /**
     * Update the given station.
     */
    public function update(UpdateStationRequest $request, Station $station): RedirectResponse
    {
        $station->update($request->validated());

        return redirect()
            ->route('admin.stations.show', $station)
            ->with('status', 'Estação atualizada com sucesso.');
    }

    /**
     * Remove the given station.
     */
    public function destroy(Station $station): RedirectResponse
    {
        $station->delete();

        return redirect()
            ->route('admin.stations.index')
            ->with('status', 'Estação excluída com sucesso.');
    }

    /**
     * The distinct non empty values of a column, ordered alphabetically.
     *
     * @return list<string>
     */
    private function distinctValues(string $column): array
    {
        return Station::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }
}
