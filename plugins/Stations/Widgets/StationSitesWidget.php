<?php

namespace Plugins\Stations\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\Stations\Models\Station;

class StationSitesWidget extends Widget
{
    public function id(): string
    {
        return 'stations.sites';
    }

    public function title(): string
    {
        return 'Sites';
    }

    public function view(): string
    {
        return 'stations::widgets.sites';
    }

    public function order(): int
    {
        return 35;
    }

    public function columnSpan(): int
    {
        return 2;
    }

    /**
     * The data passed to the widget view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        $search = mb_substr(trim((string) $request->query('site_search', '')), 0, 100);

        $query = Station::query()
            ->select('site_id')
            ->selectRaw('COUNT(*) as stations_count')
            ->selectRaw('MIN(municipio) as municipio')
            ->selectRaw('MIN(estado) as estado')
            ->selectRaw('MAX(updated_at) as last_updated_at')
            ->groupBy('site_id');

        if ($search !== '') {
            $query->where('site_id', 'like', "%{$search}%");
        }

        return [
            'sites' => $query
                ->orderByDesc('last_updated_at')
                ->orderBy('site_id')
                ->limit(8)
                ->get(),
            'siteSearch' => $search,
            'totalSites' => Station::query()->distinct()->count('site_id'),
            'editing' => $request->boolean('edit'),
        ];
    }
}
