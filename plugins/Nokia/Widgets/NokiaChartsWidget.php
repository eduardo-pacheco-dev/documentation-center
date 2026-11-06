<?php

namespace Plugins\Nokia\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;

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
        return 'Indicadores, checklist e evolução dos sites Nokia.';
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
        $total = NokiaSite::query()->count();
        $completed = NokiaSite::query()->completed()->count();
        $pending = max(0, $total - $completed);

        $checklists = collect(config('nokia.checklists', []))
            ->map(function (string $label, string $field) use ($total): array {
                $done = NokiaSite::query()->where($field, true)->count();

                return [
                    'field' => $field,
                    'label' => $label,
                    'done' => $done,
                    'percent' => $total > 0 ? round(($done / $total) * 100, 1) : 0.0,
                ];
            })
            ->values()
            ->all();

        return [
            'totals' => [
                'all' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'attachments' => NokiaSiteAttachment::query()->count(),
            ],
            'completion' => [
                [
                    'label' => 'Concluídos',
                    'count' => $completed,
                    'percent' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
                    'color' => '#10b981',
                ],
                [
                    'label' => 'Pendentes',
                    'count' => $pending,
                    'percent' => $total > 0 ? round(($pending / $total) * 100, 1) : 0.0,
                    'color' => '#f59e0b',
                ],
            ],
            'checklists' => $checklists,
            'monthly' => $this->monthlyTrend(),
        ];
    }

    /**
     * The sites created in each of the last six months.
     *
     * @return list<array{label: string, count: int}>
     */
    private function monthlyTrend(): array
    {
        $start = today()->startOfMonth()->subMonths(5);

        $created = NokiaSite::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->countBy(fn (NokiaSite $site): string => $site->created_at->format('Y-m'));

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $created): array {
                $month = $start->copy()->addMonths($offset);

                return [
                    'label' => ucfirst($month->locale('pt_BR')->translatedFormat('M')),
                    'count' => (int) $created->get($month->format('Y-m'), 0),
                ];
            })
            ->all();
    }
}
