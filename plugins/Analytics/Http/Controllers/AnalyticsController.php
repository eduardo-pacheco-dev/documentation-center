<?php

declare(strict_types=1);

namespace Plugins\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Plugins\Analytics\Http\Requests\AnalyticsFilterRequest;
use Plugins\Analytics\Models\AnalyticsRecord;

class AnalyticsController extends Controller
{
    /**
     * Show the analytics dashboard.
     */
    public function index(AnalyticsFilterRequest $request): View
    {
        $filters = $request->validated();
        $query = AnalyticsRecord::query()->filter($filters);

        return view('analytics::index', [
            'filters' => $filters,
            'records' => (clone $query)
                ->latest('data_criacao')
                ->latest('id')
                ->paginate((int) config('analytics.per_page', 20))
                ->withQueryString(),
            'stats' => [
                'total' => (clone $query)->count(),
                'accepted' => (clone $query)->accepted()->count(),
                'pending' => (clone $query)->pending()->count(),
            ],
            'suppliers' => AnalyticsRecord::query()
                ->distinct()
                ->orderBy('fornecedor')
                ->pluck('fornecedor'),
            'statuses' => AnalyticsRecord::query()
                ->distinct()
                ->orderBy('status')
                ->pluck('status'),
            'nodeNames' => AnalyticsRecord::query()
                ->distinct()
                ->orderBy('node_name')
                ->limit(200)
                ->pluck('node_name'),
        ]);
    }
}
