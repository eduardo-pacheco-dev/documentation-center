<?php

namespace App\Services\EarnedValue;

use App\Models\Baseline;
use App\Models\BaselineTask;
use App\Models\Project;
use App\Models\Task;
use App\Services\Baseline\BaselineService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calculates the earned value indicators of a project.
 *
 * The budget of every leaf task is the point of reference: PV comes from the
 * baseline (or from the current plan when no baseline was saved), EV is the
 * budget earned so far and AC is what has actually been spent.
 */
class EarnedValueService
{
    /**
     * Upper bound of points drawn on the S curve.
     */
    private const MAX_SERIES_POINTS = 200;

    public function __construct(private readonly BaselineService $baselines) {}

    /**
     * Build the earned value report of a project.
     *
     * @return array<string, mixed>
     */
    public function report(Project $project, CarbonInterface|string|null $statusDate = null, ?int $baselineId = null): array
    {
        $status = CarbonImmutable::parse($statusDate ?? $project->status_date ?? now())->endOfDay();
        $baseline = $this->resolveBaseline($project, $baselineId);
        $baselineTasks = $baseline?->tasks()->get()->keyBy('task_id') ?? collect();
        $leaves = $this->leafTasks($project);

        $bac = 0.0;
        $ev = 0.0;
        $ac = 0.0;
        $pv = 0.0;

        foreach ($leaves as $task) {
            $budget = (float) $task->budget_cost;
            $bac += $budget;
            $ev += $budget * ((float) $task->percent_complete) / 100;
            $ac += (float) $task->actual_cost;

            $pv += $this->plannedValue($task, $project, $baselineTasks, $status);
        }

        $metrics = [
            'bac' => round($bac, 2),
            'pv' => round($pv, 2),
            'ev' => round($ev, 2),
            'ac' => round($ac, 2),
            'cv' => round($ev - $ac, 2),
            'sv' => round($ev - $pv, 2),
            'cpi' => $ac > 0 ? round($ev / $ac, 2) : null,
            'spi' => $pv > 0 ? round($ev / $pv, 2) : null,
            'eac' => null,
            'etc' => null,
            'vac' => null,
            'tcpi' => null,
            'planned_percent' => $bac > 0 ? round($pv / $bac * 100, 2) : 0.0,
            'earned_percent' => $bac > 0 ? round($ev / $bac * 100, 2) : 0.0,
        ];

        if ($ac > 0 && $ev > 0) {
            $eac = $bac / ($ev / $ac);
            $metrics['eac'] = round($eac, 2);
            $metrics['etc'] = round($eac - $ac, 2);
            $metrics['vac'] = round($bac - $eac, 2);
            $metrics['tcpi'] = $bac - $ac > 0 ? round(($bac - $ev) / ($bac - $ac), 2) : null;
        }

        $metrics['status_date'] = $status->toDateString();
        $metrics['baseline'] = $baseline?->only(['id', 'name', 'saved_at']);
        $metrics['series'] = $this->series($project, $leaves, $baselineTasks, $status);

        return $metrics;
    }

    /**
     * The baseline used by the report.
     */
    private function resolveBaseline(Project $project, ?int $baselineId): ?Baseline
    {
        if ($baselineId !== null) {
            return $project->baselines()->whereKey($baselineId)->first();
        }

        return $project->baselines()->orderByDesc('saved_at')->orderByDesc('id')->first();
    }

    /**
     * The tasks that own the budget of the project.
     *
     * @return list<Task>
     */
    private function leafTasks(Project $project): array
    {
        $tasks = $project->tasks()->get();
        $parents = $tasks->pluck('parent_id')->filter()->unique();

        return $tasks
            ->reject(fn (Task $task) => $parents->contains($task->getKey()))
            ->values()
            ->all();
    }

    /**
     * The budgeted cost of work scheduled for a task at a date.
     *
     * @param  Collection<int, BaselineTask>  $baselineTasks
     */
    private function plannedValue(Task $task, Project $project, Collection $baselineTasks, CarbonImmutable $status): float
    {
        $snapshot = $baselineTasks->get($task->getKey());

        $start = CarbonImmutable::parse($snapshot?->start_at ?? $task->start_at ?? $project->start_date);
        $finish = CarbonImmutable::parse($snapshot?->finish_at ?? $task->finish_at ?? $project->start_date);
        $budget = (float) ($snapshot?->budget_cost ?? $task->budget_cost);

        return BaselineService::plannedValueAt($start, $finish, $budget, $status);
    }

    /**
     * The PV, EV and AC curve of the project.
     *
     * @param  list<Task>  $leaves
     * @param  Collection<int, BaselineTask>  $baselineTasks
     * @return list<array{date: string, pv: float, ev: float, ac: float}>
     */
    private function series(Project $project, array $leaves, Collection $baselineTasks, CarbonImmutable $status): array
    {
        $start = CarbonImmutable::parse($project->start_date)->startOfDay();

        $end = $status;
        foreach ($leaves as $task) {
            if ($task->finish_at !== null && $task->finish_at->greaterThan($end)) {
                $end = CarbonImmutable::parse($task->finish_at)->endOfDay();
            }
        }

        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->addDay();
        }

        $totalDays = max(1, (int) ceil(($end->getTimestamp() - $start->getTimestamp()) / 86400));
        $step = max(1, (int) ceil($totalDays / self::MAX_SERIES_POINTS));

        $series = [];

        for ($offset = 0; $offset <= $totalDays; $offset += $step) {
            $point = $start->addDays($offset)->endOfDay();
            $pv = 0.0;
            $ev = 0.0;

            foreach ($leaves as $task) {
                $budget = (float) $task->budget_cost;

                $pv += $this->plannedValue($task, $project, $baselineTasks, $point);
                $ev += $this->earnedAt($task, $budget, $point);
            }

            $series[] = [
                'date' => $point->toDateString(),
                'pv' => round($pv, 2),
                'ev' => round($ev, 2),
                'ac' => round($this->actualCostAt($leaves, $point), 2),
            ];
        }

        return $series;
    }

    /**
     * The budget earned by a task at a date, spread over its effective span.
     */
    private function earnedAt(Task $task, float $budget, CarbonImmutable $point): float
    {
        if ($budget <= 0) {
            return 0.0;
        }

        $earned = $budget * ((float) $task->percent_complete) / 100;

        if ($earned <= 0) {
            return 0.0;
        }

        $start = $task->actual_start_at ?? $task->start_at;
        $finish = $task->actual_finish_at ?? $task->finish_at;

        if ($start === null || $finish === null) {
            return 0.0;
        }

        $start = CarbonImmutable::parse($start);
        $finish = CarbonImmutable::parse($finish);

        if ($point->gte($finish)) {
            return round($earned, 2);
        }

        if ($point->lte($start)) {
            return 0.0;
        }

        $span = $finish->getTimestamp() - $start->getTimestamp();

        if ($span <= 0) {
            return round($earned, 2);
        }

        return round($earned * (($point->getTimestamp() - $start->getTimestamp()) / $span), 2);
    }

    /**
     * The actual cost incurred by a project at a date.
     *
     * @param  list<Task>  $leaves
     */
    private function actualCostAt(array $leaves, CarbonImmutable $point): float
    {
        $total = 0.0;
        $firstActual = null;

        foreach ($leaves as $task) {
            $total += (float) $task->actual_cost;

            if ($task->actual_start_at !== null && ($firstActual === null || $task->actual_start_at->lessThan($firstActual))) {
                $firstActual = $task->actual_start_at;
            }
        }

        if ($total <= 0 || $firstActual === null) {
            return 0.0;
        }

        if ($point->gte($firstActual)) {
            return $total;
        }

        return 0.0;
    }
}
