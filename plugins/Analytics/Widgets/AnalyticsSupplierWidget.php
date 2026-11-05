<?php

declare(strict_types=1);

namespace Plugins\Analytics\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\Analytics\Models\AnalyticsRecord;

class AnalyticsSupplierWidget extends Widget
{
    public function id(): string
    {
        return 'analytics.supplier';
    }

    public function title(): string
    {
        return 'Fornecedores e etapas';
    }

    public function description(): ?string
    {
        return 'Distribuição das OS por fornecedor e etapa do processo.';
    }

    public function icon(): ?string
    {
        return 'building-office';
    }

    public function view(): string
    {
        return 'analytics::widgets.supplier';
    }

    public function order(): int
    {
        return 65;
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
            'suppliers' => AnalyticsRecord::query()
                ->select('fornecedor')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('fornecedor')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'stages' => AnalyticsRecord::query()
                ->select('etapa_processo')
                ->selectRaw('COUNT(*) as total')
                ->whereNotNull('etapa_processo')
                ->groupBy('etapa_processo')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
            'total' => AnalyticsRecord::query()->count(),
        ];
    }
}
