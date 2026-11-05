<?php

declare(strict_types=1);

namespace Plugins\Analytics\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\Analytics\Models\AnalyticsRecord;

class AnalyticsOverviewWidget extends Widget
{
    public function id(): string
    {
        return 'analytics.overview';
    }

    public function title(): string
    {
        return 'Status das OS';
    }

    public function description(): ?string
    {
        return 'Resumo dos status das ordens de serviço importadas.';
    }

    public function icon(): ?string
    {
        return 'chart-bar';
    }

    public function view(): string
    {
        return 'analytics::widgets.overview';
    }

    public function order(): int
    {
        return 60;
    }

    public function columnSpan(): int
    {
        return 2;
    }

    public function visible(Request $request): bool
    {
        return (bool) $request->user()?->is_admin;
    }

    /**
     * The data passed to the widget view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [
            'total' => AnalyticsRecord::query()->count(),
            'accepted' => AnalyticsRecord::query()->accepted()->count(),
            'pending' => AnalyticsRecord::query()->pending()->count(),
            'statuses' => AnalyticsRecord::query()
                ->select('status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('status')
                ->orderByDesc('total')
                ->limit(6)
                ->get(),
        ];
    }
}
