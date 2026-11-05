<?php

namespace Plugins\FollowUp\Widgets;

use App\Dashboard\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Models\FollowUp;

class FollowUpChartsWidget extends Widget
{
    /**
     * The colors used by each status in the charts.
     *
     * @var array<string, string>
     */
    private const STATUS_COLORS = [
        FollowUpStatus::Pending->value => '#f59e0b',
        FollowUpStatus::InProgress->value => '#6366f1',
        FollowUpStatus::Completed->value => '#10b981',
        FollowUpStatus::Cancelled->value => '#ef4444',
    ];

    /**
     * The colors used by each priority in the charts.
     *
     * @var array<string, string>
     */
    private const PRIORITY_COLORS = [
        FollowUpPriority::Low->value => '#94a3b8',
        FollowUpPriority::Medium->value => '#6366f1',
        FollowUpPriority::High->value => '#f59e0b',
        FollowUpPriority::Urgent->value => '#ef4444',
    ];

    public function id(): string
    {
        return 'follow-up.charts';
    }

    public function title(): string
    {
        return 'Painel de gráficos';
    }

    public function view(): string
    {
        return 'follow-up::widgets.charts';
    }

    public function order(): int
    {
        return 46;
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
        $statusCounts = FollowUp::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $priorityCounts = FollowUp::query()
            ->selectRaw('priority, count(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        $total = (int) $statusCounts->sum();
        $completed = (int) $statusCounts->get(FollowUpStatus::Completed->value, 0);
        $cancelled = (int) $statusCounts->get(FollowUpStatus::Cancelled->value, 0);

        return [
            'totals' => [
                'all' => $total,
                'open' => max(0, $total - $completed - $cancelled),
                'overdue' => FollowUp::query()->overdue()->count(),
                'completed' => $completed,
            ],
            'statusDistribution' => $this->distribution(
                collect(FollowUpStatus::cases())->mapWithKeys(
                    fn (FollowUpStatus $status): array => [$status->value => $status->label()],
                ),
                $statusCounts,
                self::STATUS_COLORS,
                $total,
            ),
            'priorityDistribution' => $this->distribution(
                collect(FollowUpPriority::cases())->mapWithKeys(
                    fn (FollowUpPriority $priority): array => [$priority->value => $priority->label()],
                ),
                $priorityCounts,
                self::PRIORITY_COLORS,
                (int) $priorityCounts->sum(),
            ),
            'monthly' => $this->monthlyTrend(),
        ];
    }

    /**
     * Build the chart rows for a grouped count.
     *
     * @param  Collection<string, string>  $labels
     * @param  Collection<string, int|string>  $counts
     * @param  array<string, string>  $colors
     * @return list<array{value: string, label: string, count: int, percent: float, color: string}>
     */
    private function distribution(Collection $labels, Collection $counts, array $colors, int $total): array
    {
        return $labels
            ->map(function (string $label, string $value) use ($counts, $colors, $total): array {
                $count = (int) $counts->get($value, 0);

                return [
                    'value' => $value,
                    'label' => $label,
                    'count' => $count,
                    'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
                    'color' => $colors[$value] ?? '#94a3b8',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The created and completed follow-ups for the last six months.
     *
     * @return list<array{label: string, created: int, completed: int}>
     */
    private function monthlyTrend(): array
    {
        $start = today()->startOfMonth()->subMonths(5);

        $created = FollowUp::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->countBy(fn (FollowUp $followUp): string => $followUp->created_at->format('Y-m'));

        $completed = FollowUp::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $start)
            ->get(['completed_at'])
            ->countBy(fn (FollowUp $followUp): string => $followUp->completed_at->format('Y-m'));

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $created, $completed): array {
                $month = $start->copy()->addMonths($offset);
                $key = $month->format('Y-m');

                return [
                    'label' => ucfirst($month->locale('pt_BR')->translatedFormat('M')),
                    'created' => (int) $created->get($key, 0),
                    'completed' => (int) $completed->get($key, 0),
                ];
            })
            ->all();
    }
}
