<?php

namespace Plugins\Analytics\Services;

use App\Enums\AgendaEventStatus;
use App\Enums\ProjectStatus;
use App\Enums\WorkOrderStatus;
use App\Models\AgendaEvent;
use App\Models\Document;
use App\Models\Form;
use App\Models\Project;
use App\Models\TodoList;
use App\Models\TodoTask;
use App\Models\User;
use App\Models\WorkOrder;
use App\Plugins\PluginRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Plugins\Analytics\Models\AnalyticsSnapshot;

/**
 * Collects the metrics shown by the analytics dashboard.
 *
 * Every query filters by the given user unless the instance is global, in
 * which case the data of all users is aggregated. The core tables are only
 * read, never changed.
 */
class AnalyticsMetrics
{
    /**
     * Status colors used by the charts.
     *
     * @var array<string, string>
     */
    private const COLORS = [
        'active' => '#4f46e5',
        'completed' => '#10b981',
        'archived' => '#94a3b8',
        'open' => '#6366f1',
        'in_progress' => '#0ea5e9',
        'on_hold' => '#f59e0b',
        'cancelled' => '#ef4444',
        'confirmed' => '#10b981',
        'tentative' => '#f59e0b',
        'pending' => '#f59e0b',
        'approved' => '#10b981',
        'rejected' => '#ef4444',
        'filled' => '#0ea5e9',
        'draft' => '#94a3b8',
    ];

    /** @var array<string, string>|null */
    private ?array $bucketKeys = null;

    public function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ?User $user = null,
    ) {}

    /**
     * Whether the metrics aggregate every user.
     */
    public function isGlobal(): bool
    {
        return $this->user === null;
    }

    /**
     * The headline indicators.
     *
     * @return array<string, array<string, int>|null>
     */
    public function summary(): array
    {
        $projects = $this->query(Project::class);
        $workOrders = $this->query(WorkOrder::class);
        $tasks = $this->query(TodoTask::class);
        $forms = $this->query(Form::class);
        $documents = $this->query(Document::class);
        $agenda = $this->query(AgendaEvent::class);

        return [
            'projects' => [
                'total' => (clone $projects)->count(),
                'active' => (clone $projects)->where('status', ProjectStatus::Active->value)->count(),
                'completed' => (clone $projects)->where('status', ProjectStatus::Completed->value)->count(),
                'created' => (clone $projects)->whereBetween('created_at', [$this->from, $this->to])->count(),
            ],
            'work_orders' => [
                'total' => (clone $workOrders)->count(),
                'open' => (clone $workOrders)->whereIn('status', [WorkOrderStatus::Open->value, WorkOrderStatus::InProgress->value])->count(),
                'completed' => (clone $workOrders)->where('status', WorkOrderStatus::Completed->value)->count(),
                'created' => (clone $workOrders)->whereBetween('created_at', [$this->from, $this->to])->count(),
                'closed' => (clone $workOrders)->whereBetween('completed_at', [$this->from, $this->to])->count(),
            ],
            'tasks' => [
                'total' => (clone $tasks)->count(),
                'pending' => (clone $tasks)->whereNull('completed_at')->count(),
                'completed' => (clone $tasks)->whereNotNull('completed_at')->count(),
                'completed_in_period' => (clone $tasks)->whereBetween('completed_at', [$this->from, $this->to])->count(),
            ],
            'forms' => [
                'total' => (clone $forms)->count(),
                'pending' => (clone $forms)->whereIn('status', ['Pendente', 'Em preenchimento'])->count(),
                'approved' => (clone $forms)->where('status', 'Aprovado')->count(),
            ],
            'documents' => [
                'total' => (clone $documents)->count(),
                'in_period' => (clone $documents)->whereBetween('created_at', [$this->from, $this->to])->count(),
                'size' => (int) (clone $documents)->sum('size'),
            ],
            'agenda' => [
                'total' => (clone $agenda)->count(),
                'in_period' => (clone $agenda)->whereBetween('start_at', [$this->from, $this->to])->count(),
            ],
            'stations' => $this->stationsSummary(),
        ];
    }

    /**
     * Records created per month inside the period.
     *
     * @return array{labels: list<string>, projects: list<int>, work_orders: list<int>, documents: list<int>}
     */
    public function series(): array
    {
        return [
            'labels' => array_values($this->buckets()),
            'projects' => $this->bucketCounts(Project::class),
            'work_orders' => $this->bucketCounts(WorkOrder::class),
            'documents' => $this->bucketCounts(Document::class),
        ];
    }

    /**
     * Work order distribution by status.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    public function workOrderStatuses(): array
    {
        return $this->statusBreakdown(
            $this->query(WorkOrder::class),
            WorkOrderStatus::cases(),
        );
    }

    /**
     * Project distribution by status.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    public function projectStatuses(): array
    {
        return $this->statusBreakdown(
            $this->query(Project::class),
            ProjectStatus::cases(),
        );
    }

    /**
     * Agenda distribution by status inside the period.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    public function agendaStatuses(): array
    {
        return $this->statusBreakdown(
            $this->query(AgendaEvent::class)->whereBetween('start_at', [$this->from, $this->to]),
            AgendaEventStatus::cases(),
        );
    }

    /**
     * Form distribution by status.
     *
     * @return list<array{label: string, value: int, color: string}>
     */
    public function formStatuses(): array
    {
        $statuses = [
            'Pendente' => 'pending',
            'Em preenchimento' => 'filled',
            'Preenchido' => 'in_progress',
            'Aprovado' => 'approved',
            'Reprovado' => 'cancelled',
        ];

        $query = $this->query(Form::class);

        $breakdown = [];

        foreach ($statuses as $label => $color) {
            $value = (clone $query)->where('status', $label)->count();

            if ($value > 0) {
                $breakdown[] = [
                    'label' => $label,
                    'value' => $value,
                    'color' => self::COLORS[$color] ?? '#94a3b8',
                ];
            }
        }

        return $breakdown;
    }

    /**
     * The pending and completed tasks split.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function taskSplit(): array
    {
        $query = $this->query(TodoTask::class);

        return [
            'labels' => ['Pendentes', 'Concluídas'],
            'values' => [
                (clone $query)->whereNull('completed_at')->count(),
                (clone $query)->whereNotNull('completed_at')->count(),
            ],
        ];
    }

    /**
     * The lists with the most tasks.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function tasksByList(): array
    {
        $lists = TodoList::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->withCount('tasks')
            ->orderByDesc('tasks_count')
            ->limit((int) config('analytics.ranking_limit', 6))
            ->get(['id', 'name']);

        return [
            'labels' => $lists->pluck('name')->all(),
            'values' => $lists->pluck('tasks_count')->map(fn ($count): int => (int) $count)->all(),
        ];
    }

    /**
     * The projects with the most tasks.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function topProjects(): array
    {
        $projects = Project::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->withCount('tasks')
            ->orderByDesc('tasks_count')
            ->limit((int) config('analytics.ranking_limit', 6))
            ->get(['id', 'name', 'code']);

        return [
            'labels' => $projects->map(fn (Project $project): string => $project->code
                ? $project->code.' — '.$project->name
                : $project->name)->all(),
            'values' => $projects->pluck('tasks_count')->map(fn ($count): int => (int) $count)->all(),
        ];
    }

    /**
     * The latest stored snapshots for the current scope.
     *
     * @return list<array{captured_on: string, metrics: array<string, mixed>}>
     */
    public function snapshots(): array
    {
        return AnalyticsSnapshot::query()
            ->where('user_id', $this->user?->getKey())
            ->latest('captured_on')
            ->limit((int) config('analytics.snapshots_to_show', 10))
            ->get()
            ->map(fn (AnalyticsSnapshot $snapshot): array => [
                'captured_on' => Carbon::parse((string) $snapshot->captured_on)->format('d/m/Y'),
                'metrics' => (array) $snapshot->metrics,
            ])
            ->all();
    }

    /**
     * The snapshot totals as a chart series, when snapshots exist.
     *
     * @return array{labels: list<string>, projects: list<int>, work_orders: list<int>}|null
     */
    public function snapshotSeries(): ?array
    {
        $snapshots = $this->snapshots();

        if ($snapshots === []) {
            return null;
        }

        $snapshots = array_reverse($snapshots);

        return [
            'labels' => array_column($snapshots, 'captured_on'),
            'projects' => array_map(fn (array $snapshot): int => (int) ($snapshot['metrics']['projects']['total'] ?? 0), $snapshots),
            'work_orders' => array_map(fn (array $snapshot): int => (int) ($snapshot['metrics']['work_orders']['total'] ?? 0), $snapshots),
        ];
    }

    /**
     * The station indicators, only when the Stations plugin is available.
     *
     * @return array{total: int, active: int, created: int}|null
     */
    private function stationsSummary(): ?array
    {
        if (! app(PluginRepository::class)->has('stations')) {
            return null;
        }

        $model = 'Plugins\\Stations\\Models\\Station';

        if (! class_exists($model)) {
            return null;
        }

        $query = $model::query();

        if ($this->user !== null) {
            $query->where('user_id', $this->user->getKey());
        }

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'created' => (clone $query)->whereBetween('created_at', [$this->from, $this->to])->count(),
        ];
    }

    /**
     * Start a query for the given model, scoped to the user when needed.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @return Builder<TModel>
     */
    private function query(string $model): Builder
    {
        $query = $model::query();

        if ($this->user !== null) {
            $query->where('user_id', $this->user->getKey());
        }

        return $query;
    }

    /**
     * The chart buckets of the period, keyed by the grouping format.
     *
     * Periods up to 31 days are grouped by day, longer periods by month.
     *
     * @return array<string, string>
     */
    private function buckets(): array
    {
        if ($this->bucketKeys !== null) {
            return $this->bucketKeys;
        }

        $buckets = [];

        if ($this->from->diffInDays($this->to) <= 31) {
            for ($cursor = $this->from->startOfDay(); $cursor->lessThanOrEqualTo($this->to); $cursor = $cursor->addDay()) {
                $buckets[$cursor->format('Y-m-d')] = $cursor->format('d/m');
            }

            return $this->bucketKeys = $buckets;
        }

        $cursor = $this->from->startOfMonth();
        $end = $this->to->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $buckets[$cursor->format('Y-m')] = $cursor->format('m/Y');
            $cursor = $cursor->addMonth();
        }

        return $this->bucketKeys = $buckets;
    }

    /**
     * @return list<int>
     */
    private function bucketCounts(string $model): array
    {
        $buckets = $this->buckets();
        $format = $this->from->diffInDays($this->to) <= 31 ? 'Y-m-d' : 'Y-m';

        $counts = $this->query($model)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->pluck('created_at')
            ->groupBy(fn ($date): string => Carbon::parse($date)->format($format))
            ->map(fn ($group): int => $group->count());

        return array_values(array_map(
            fn (string $key): int => (int) ($counts[$key] ?? 0),
            array_keys($buckets),
        ));
    }

    /**
     * @param  array<int, ProjectStatus|WorkOrderStatus|AgendaEventStatus>  $statuses
     * @return list<array{label: string, value: int, color: string}>
     */
    private function statusBreakdown(Builder $query, array $statuses): array
    {
        $breakdown = [];

        foreach ($statuses as $status) {
            $value = (clone $query)->where('status', $status->value)->count();

            if ($value > 0) {
                $breakdown[] = [
                    'label' => $status->label(),
                    'value' => $value,
                    'color' => self::COLORS[$status->value] ?? '#94a3b8',
                ];
            }
        }

        return $breakdown;
    }
}
