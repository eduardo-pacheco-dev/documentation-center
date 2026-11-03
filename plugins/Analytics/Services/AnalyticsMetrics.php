<?php

namespace Plugins\Analytics\Services;

use App\Models\User;
use App\Plugins\PluginRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Plugins\Agenda\Enums\AgendaEventStatus;
use Plugins\Agenda\Models\AgendaEvent;
use Plugins\Analytics\Models\AnalyticsAflRecord;
use Plugins\Analytics\Models\AnalyticsNokiaRecord;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Analytics\Models\AnalyticsSnapshot;
use Plugins\Analytics\Support\AnalyticsDatabase;
use Plugins\Files\Models\Document;
use Plugins\Forms\Models\Form;
use Plugins\Projects\Enums\ProjectStatus;
use Plugins\Projects\Models\Project;
use Plugins\Tasks\Models\TodoList;
use Plugins\Tasks\Models\TodoTask;
use Plugins\WorkOrders\Enums\WorkOrderStatus;
use Plugins\WorkOrders\Models\WorkOrder;

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

    private ?bool $hasRecordsTable = null;

    private ?bool $hasAflTable = null;

    private ?bool $hasNokiaTable = null;

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
            'records' => $this->recordsSummary(),
            'stations' => $this->stationsSummary(),
        ];
    }

    /**
     * Records created per month inside the period.
     *
     * @return array{labels: list<string>, records: list<int>, afl: list<int>}
     */
    public function series(): array
    {
        return [
            'labels' => array_values($this->buckets()),
            'records' => $this->recordBucketCounts(),
            'afl' => $this->aflBucketCounts(),
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
        if (! $this->hasSnapshotsTable()) {
            return [];
        }

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
     * @return array{labels: list<string>, records: list<int>, afl: list<int>, nokia: list<int>}|null
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
            'records' => array_map(fn (array $snapshot): int => (int) ($snapshot['metrics']['records']['total'] ?? 0), $snapshots),
            'afl' => array_map(fn (array $snapshot): int => (int) ($snapshot['metrics']['afl']['total'] ?? 0), $snapshots),
            'nokia' => array_map(fn (array $snapshot): int => (int) ($snapshot['metrics']['nokia']['total'] ?? 0), $snapshots),
        ];
    }

    /**
     * The imported FAMs, AFL and Nokia indicators stored in snapshots.
     *
     * @return array{records: array{total: int, in_period: int, versions: int}|null, afl: array{total: int, in_period: int, versions: int}|null, nokia: array{total: int, in_period: int, versions: int}|null}
     */
    public function importSummary(): array
    {
        return [
            'records' => $this->recordsSummary(),
            'afl' => $this->aflSummary(),
            'nokia' => $this->nokiaSummary(),
        ];
    }

    /**
     * The imported OC/OS records indicators.
     *
     * @return array{total: int, in_period: int}|null
     */
    public function recordsSummary(): ?array
    {
        if (! $this->hasRecordsTable()) {
            return null;
        }

        $query = $this->latestRecords();

        return [
            'total' => (clone $query)->count(),
            'in_period' => (clone $query)
                ->whereBetween('created_on', [$this->from->toDateString(), $this->to->toDateString()])
                ->count(),
            'versions' => AnalyticsRecord::query()
                ->when($this->user !== null, fn (Builder $inner) => $inner->where('user_id', $this->user->getKey()))
                ->count(),
        ];
    }

    /**
     * The imported records grouped by process stage.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function recordsByStage(): array
    {
        return $this->recordBreakdown('process_stage');
    }

    /**
     * The imported records grouped by status.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function recordsByStatus(): array
    {
        return $this->recordBreakdown('status');
    }

    /**
     * The imported records grouped by supplier.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function recordsBySupplier(): array
    {
        return $this->recordBreakdown('supplier');
    }

    /**
     * The latest imported records for the current scope.
     *
     * @return list<array<string, string|null>>
     */
    public function recentRecords(int $limit = 8): array
    {
        if (! $this->hasRecordsTable()) {
            return [];
        }

        $records = $this->latestRecords()
            ->orderByDesc('created_on')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $counts = $this->versionCounts($records);

        return $records
            ->map(fn (AnalyticsRecord $record): array => [
                'oc' => $record->oc,
                'os' => $record->os,
                'created_on' => $record->created_on?->format('d/m/Y'),
                'supplier' => $record->supplier,
                'process_stage' => $record->process_stage,
                'city' => $record->city,
                'state' => $record->state,
                'node_name' => $record->node_name,
                'location' => $record->location,
                'status' => $record->status,
                'versions' => $counts[$record->oc.'|'.$record->os] ?? 1,
            ])
            ->all();
    }

    /**
     * The latest changes recorded for the imported FAMs, newest first.
     *
     * @return list<array<string, string|null|int>>
     */
    public function recentRecordChanges(int $limit = 8): array
    {
        if (! $this->hasRecordsTable()) {
            return [];
        }

        $firstIds = AnalyticsRecord::query()
            ->selectRaw('MIN(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('oc', 'os');

        return AnalyticsRecord::query()
            ->whereNotIn('id', $firstIds)
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsRecord $record): array => [
                'oc' => $record->oc,
                'os' => $record->os,
                'status' => $record->status,
                'process_stage' => $record->process_stage,
                'imported_at' => $record->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }

    /**
     * The latest FAMs records, paginated for the API.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function recordsList(int $perPage = 25, ?string $search = null): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        if (! $this->hasRecordsTable()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $paginator = $this->latestRecords()
            ->when($search !== null && $search !== '', fn (Builder $query) => $query->where(function (Builder $inner) use ($search): void {
                $inner->where('oc', 'like', "%{$search}%")
                    ->orWhere('os', 'like', "%{$search}%")
                    ->orWhere('node_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('process_stage', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_on')
            ->orderByDesc('id')
            ->paginate($perPage);

        $counts = $this->versionCounts(collect($paginator->items()));

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (AnalyticsRecord $record): array => [
                ...$record->toArray(),
                'created_on' => $record->created_on?->toDateString(),
                'versions' => $counts[$record->oc.'|'.$record->os] ?? 1,
            ]),
        );

        return $paginator;
    }

    /**
     * Every stored version of the given OC + OS pair.
     *
     * @return list<array<string, mixed>>
     */
    public function recordHistory(string $oc, string $os): array
    {
        if (! $this->hasRecordsTable()) {
            return [];
        }

        return AnalyticsRecord::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->where('oc', $oc)
            ->where('os', $os)
            ->orderBy('id')
            ->get()
            ->map(fn (AnalyticsRecord $record): array => [
                ...$record->toArray(),
                'created_on' => $record->created_on?->toDateString(),
                'imported_at' => $record->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * The latest AFL records, paginated for the API.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function aflRecordsList(int $perPage = 25, ?string $search = null): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        if (! $this->hasAflTable()) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $paginator = $this->latestAflRecords()
            ->when($search !== null && $search !== '', fn (Builder $query) => $query->where(function (Builder $inner) use ($search): void {
                $inner->where('code', 'like', "%{$search}%")
                    ->orWhere('project', 'like', "%{$search}%")
                    ->orWhere('site_id_a', 'like', "%{$search}%")
                    ->orWhere('site_id_b', 'like', "%{$search}%")
                    ->orWhere('supervisor', 'like', "%{$search}%")
                    ->orWhere('general_status', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_on')
            ->orderByDesc('id')
            ->paginate($perPage);

        $counts = $this->aflVersionCounts(collect($paginator->items()));

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (AnalyticsAflRecord $record): array => [
                ...$record->toArray(),
                'created_on' => $record->created_on?->toDateString(),
                'versions' => $counts[(string) $record->code] ?? 1,
            ]),
        );

        return $paginator;
    }

    /**
     * Every stored version of the given AFL code.
     *
     * @return list<array<string, mixed>>
     */
    public function aflHistory(string $code): array
    {
        if (! $this->hasAflTable()) {
            return [];
        }

        return AnalyticsAflRecord::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->where('code', $code)
            ->orderBy('id')
            ->get()
            ->map(fn (AnalyticsAflRecord $record): array => [
                ...$record->toArray(),
                'created_on' => $record->created_on?->toDateString(),
                'imported_at' => $record->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * The latest recorded version of every OC + OS pair.
     *
     * @return Builder<AnalyticsRecord>
     */
    private function latestRecords(): Builder
    {
        $latestIds = AnalyticsRecord::query()
            ->selectRaw('MAX(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('oc', 'os');

        return AnalyticsRecord::query()->whereIn('id', $latestIds);
    }

    /**
     * Count how many versions exist for each OC + OS pair of the given records.
     *
     * @param  Collection<int, AnalyticsRecord>  $records
     * @return array<string, int>
     */
    private function versionCounts($records): array
    {
        if ($records->isEmpty()) {
            return [];
        }

        $counts = AnalyticsRecord::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->where(function (Builder $query) use ($records): void {
                foreach ($records as $record) {
                    $query->orWhere(fn (Builder $inner) => $inner
                        ->where('oc', $record->oc)
                        ->where('os', $record->os));
                }
            })
            ->selectRaw('oc, os, count(*) as total')
            ->groupBy('oc', 'os')
            ->get();

        return $counts
            ->mapWithKeys(fn ($row): array => [$row->oc.'|'.$row->os => (int) $row->total])
            ->all();
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function recordBreakdown(string $column): array
    {
        if (! $this->hasRecordsTable()) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $this->latestRecords()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("{$column} as label, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit((int) config('analytics.ranking_limit', 6))
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($label): string => (string) $label)->all(),
            'values' => $rows->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
    }

    /**
     * @return list<int>
     */
    private function recordBucketCounts(): array
    {
        if (! $this->hasRecordsTable()) {
            return array_fill(0, count($this->buckets()), 0);
        }

        $format = $this->from->diffInDays($this->to) <= 31 ? 'Y-m-d' : 'Y-m';

        $counts = $this->latestRecords()
            ->whereBetween('created_on', [$this->from->toDateString(), $this->to->toDateString()])
            ->pluck('created_on')
            ->groupBy(fn ($date): string => Carbon::parse($date)->format($format))
            ->map(fn ($group): int => $group->count());

        return array_values(array_map(
            fn (string $key): int => (int) ($counts[$key] ?? 0),
            array_keys($this->buckets()),
        ));
    }

    private function hasRecordsTable(): bool
    {
        return $this->hasRecordsTable ??= AnalyticsDatabase::schema()->hasTable('analytics_records');
    }

    /**
     * @return list<int>
     */
    private function aflBucketCounts(): array
    {
        if (! $this->hasAflTable()) {
            return array_fill(0, count($this->buckets()), 0);
        }

        $format = $this->from->diffInDays($this->to) <= 31 ? 'Y-m-d' : 'Y-m';

        $counts = $this->latestAflRecords()
            ->whereBetween('created_on', [$this->from->toDateString(), $this->to->toDateString()])
            ->pluck('created_on')
            ->groupBy(fn ($date): string => Carbon::parse($date)->format($format))
            ->map(fn ($group): int => $group->count());

        return array_values(array_map(
            fn (string $key): int => (int) ($counts[$key] ?? 0),
            array_keys($this->buckets()),
        ));
    }

    /**
     * The imported AFL indicators.
     *
     * @return array{total: int, in_period: int, versions: int}|null
     */
    public function aflSummary(): ?array
    {
        if (! $this->hasAflTable()) {
            return null;
        }

        $query = $this->latestAflRecords();

        return [
            'total' => (clone $query)->count(),
            'in_period' => (clone $query)
                ->whereBetween('created_on', [$this->from->toDateString(), $this->to->toDateString()])
                ->count(),
            'versions' => AnalyticsAflRecord::query()
                ->when($this->user !== null, fn (Builder $inner) => $inner->where('user_id', $this->user->getKey()))
                ->count(),
        ];
    }

    /**
     * The AFL records grouped by general status.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function aflByStatus(): array
    {
        return $this->aflBreakdown('general_status');
    }

    /**
     * The AFL records grouped by billing status.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function aflByBillingStatus(): array
    {
        return $this->aflBreakdown('billing_status');
    }

    /**
     * The AFL records grouped by project.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function aflByProject(): array
    {
        return $this->aflBreakdown('project');
    }

    /**
     * The latest AFL records for the current scope.
     *
     * @return list<array<string, mixed>>
     */
    public function recentAflRecords(int $limit = 8): array
    {
        if (! $this->hasAflTable()) {
            return [];
        }

        $records = $this->latestAflRecords()
            ->orderByDesc('created_on')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $counts = $this->aflVersionCounts($records);

        return $records
            ->map(fn (AnalyticsAflRecord $record): array => [
                'code' => $record->code,
                'project' => $record->project,
                'site_a' => $record->site_id_a,
                'site_b' => $record->site_id_b,
                'supervisor' => $record->supervisor,
                'general_status' => $record->general_status,
                'billing_status' => $record->billing_status,
                'created_on' => $record->created_on?->format('d/m/Y'),
                'versions' => $counts[$record->code] ?? 1,
            ])
            ->all();
    }

    /**
     * The latest AFL changes, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function recentAflChanges(int $limit = 8): array
    {
        if (! $this->hasAflTable()) {
            return [];
        }

        $firstIds = AnalyticsAflRecord::query()
            ->selectRaw('MIN(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('code');

        return AnalyticsAflRecord::query()
            ->whereNotIn('id', $firstIds)
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsAflRecord $record): array => [
                'code' => $record->code,
                'general_status' => $record->general_status,
                'billing_status' => $record->billing_status,
                'imported_at' => $record->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }

    /**
     * The latest recorded version of every AFL code.
     *
     * @return Builder<AnalyticsAflRecord>
     */
    private function latestAflRecords(): Builder
    {
        $latestIds = AnalyticsAflRecord::query()
            ->selectRaw('MAX(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('code');

        return AnalyticsAflRecord::query()->whereIn('id', $latestIds);
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function aflBreakdown(string $column): array
    {
        if (! $this->hasAflTable()) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $this->latestAflRecords()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("{$column} as label, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit((int) config('analytics.ranking_limit', 6))
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($label): string => (string) $label)->all(),
            'values' => $rows->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
    }

    /**
     * @param  Collection<int, AnalyticsAflRecord>  $records
     * @return array<string, int>
     */
    private function aflVersionCounts($records): array
    {
        if ($records->isEmpty()) {
            return [];
        }

        $counts = AnalyticsAflRecord::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->whereIn('code', $records->pluck('code')->all())
            ->selectRaw('code, count(*) as total')
            ->groupBy('code')
            ->get();

        return $counts
            ->mapWithKeys(fn ($row): array => [(string) $row->code => (int) $row->total])
            ->all();
    }

    private function hasSnapshotsTable(): bool
    {
        return AnalyticsDatabase::schema()->hasTable('analytics_snapshots');
    }

    /**
     * The imported Nokia indicators.
     *
     * @return array{total: int, in_period: int, versions: int}|null
     */
    public function nokiaSummary(): ?array
    {
        if (! $this->hasNokiaTable()) {
            return null;
        }

        $query = $this->latestNokiaRecords();

        return [
            'total' => (clone $query)->count(),
            'in_period' => (clone $query)
                ->whereBetween('created_at', [$this->from, $this->to])
                ->count(),
            'versions' => AnalyticsNokiaRecord::query()
                ->when($this->user !== null, fn (Builder $inner) => $inner->where('user_id', $this->user->getKey()))
                ->count(),
        ];
    }

    /**
     * The Nokia records grouped by general status.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function nokiaByStatus(): array
    {
        return $this->nokiaBreakdown('status_geral');
    }

    /**
     * The Nokia records grouped by regional.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function nokiaByRegional(): array
    {
        return $this->nokiaBreakdown('regional');
    }

    /**
     * The latest Nokia records for the current scope.
     *
     * @return list<array<string, mixed>>
     */
    public function recentNokiaRecords(int $limit = 8): array
    {
        if (! $this->hasNokiaTable()) {
            return [];
        }

        $records = $this->latestNokiaRecords()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $counts = $this->nokiaVersionCounts($records);

        return $records
            ->map(fn (AnalyticsNokiaRecord $record): array => [
                'oc' => $record->oc,
                'site_id' => $record->site_id,
                'city' => $record->city,
                'uf' => $record->uf,
                'regional' => $record->regional,
                'project' => $record->project,
                'gap_status' => $record->gap_status,
                'macro_status' => $record->macro_status,
                'status_geral' => $record->status_geral,
                'imported_at' => $record->created_at?->format('d/m/Y H:i'),
                'versions' => $counts[$record->oc] ?? 1,
            ])
            ->all();
    }

    /**
     * The latest Nokia changes, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function recentNokiaChanges(int $limit = 8): array
    {
        if (! $this->hasNokiaTable()) {
            return [];
        }

        $firstIds = AnalyticsNokiaRecord::query()
            ->selectRaw('MIN(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('oc');

        return AnalyticsNokiaRecord::query()
            ->whereNotIn('id', $firstIds)
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (AnalyticsNokiaRecord $record): array => [
                'oc' => $record->oc,
                'site_id' => $record->site_id,
                'status_geral' => $record->status_geral,
                'imported_at' => $record->created_at?->format('d/m/Y H:i'),
            ])
            ->all();
    }

    /**
     * The latest recorded version of every Nokia OC.
     *
     * @return Builder<AnalyticsNokiaRecord>
     */
    private function latestNokiaRecords(): Builder
    {
        $latestIds = AnalyticsNokiaRecord::query()
            ->selectRaw('MAX(id)')
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->groupBy('oc');

        return AnalyticsNokiaRecord::query()->whereIn('id', $latestIds);
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function nokiaBreakdown(string $column): array
    {
        if (! $this->hasNokiaTable()) {
            return ['labels' => [], 'values' => []];
        }

        $rows = $this->latestNokiaRecords()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("{$column} as label, count(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit((int) config('analytics.ranking_limit', 6))
            ->get();

        return [
            'labels' => $rows->pluck('label')->map(fn ($label): string => (string) $label)->all(),
            'values' => $rows->pluck('total')->map(fn ($total): int => (int) $total)->all(),
        ];
    }

    /**
     * @param  Collection<int, AnalyticsNokiaRecord>  $records
     * @return array<string, int>
     */
    private function nokiaVersionCounts($records): array
    {
        if ($records->isEmpty()) {
            return [];
        }

        $counts = AnalyticsNokiaRecord::query()
            ->when($this->user !== null, fn (Builder $query) => $query->where('user_id', $this->user->getKey()))
            ->whereIn('oc', $records->pluck('oc')->all())
            ->selectRaw('oc, count(*) as total')
            ->groupBy('oc')
            ->get();

        return $counts
            ->mapWithKeys(fn ($row): array => [(string) $row->oc => (int) $row->total])
            ->all();
    }

    private function hasNokiaTable(): bool
    {
        return $this->hasNokiaTable ??= AnalyticsDatabase::schema()->hasTable('analytics_nokia_records');
    }

    private function hasAflTable(): bool
    {
        return $this->hasAflTable ??= AnalyticsDatabase::schema()->hasTable('analytics_afl_records');
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

        if (! class_exists($model) || ! Schema::hasTable('stations')) {
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
