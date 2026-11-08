<?php

namespace Plugins\Nokia\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\Nokia\Support\NokiaDashboardMetrics;

class NokiaChartsWidget extends Widget
{
    public function id(): string
    {
        return 'nokia.charts';
    }

    public function title(): string
    {
        return 'Projetos Nokia';
    }

    public function description(): ?string
    {
        return 'Aceitação, status das FAMs, aging e desempenho por regional, subcon e ROM.';
    }

    public function icon(): ?string
    {
        return 'chart-bar';
    }

    public function view(): string
    {
        return 'nokia::widgets.charts';
    }

    public function order(): int
    {
        return 50;
    }

    public function columnSpan(): int
    {
        return 3;
    }

    /**
     * The data passed to the widget view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return app(NokiaDashboardMetrics::class)->build();
    }
}
