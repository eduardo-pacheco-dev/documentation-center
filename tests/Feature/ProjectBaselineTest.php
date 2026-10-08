<?php

use App\Models\Task;
use App\Services\Baseline\BaselineService;
use App\Services\Scheduling\ProjectScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('freezes the current plan as a baseline', function () {
    $project = workingProject();
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480, 'budget_cost' => 1000]);

    app(ProjectScheduler::class)->schedule($project);

    $baseline = app(BaselineService::class)->save($project, 'Linha de base inicial');

    $snapshot = $baseline->tasks()->firstWhere('task_id', $task->getKey());

    expect($baseline->name)->toBe('Linha de base inicial')
        ->and($baseline->tasks()->count())->toBe(1)
        ->and($snapshot->start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($snapshot->finish_at->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($snapshot->budget_cost)->toEqual(1000.0);
});

it('restores the baseline back into the live plan', function () {
    $project = workingProject();
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);
    app(ProjectScheduler::class)->schedule($project);

    $baseline = app(BaselineService::class)->save($project, 'Inicial');

    $task->forceFill([
        'duration_minutes' => 1440,
        'level_delay_minutes' => 480,
    ])->save();
    app(ProjectScheduler::class)->schedule($project);

    $restored = app(BaselineService::class)->restore($project, $baseline);

    $task->refresh();

    expect($restored)->toBe(1)
        ->and($task->duration_minutes)->toBe(480)
        ->and($task->level_delay_minutes)->toBe(0)
        ->and($task->finish_at->format('Y-m-d H:i'))->toBe('2026-01-05 17:00');
});

it('reports how far the plan drifted from the baseline in days', function () {
    $project = workingProject();
    $first = Task::factory()->for($project)->create(['duration_minutes' => 480, 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['duration_minutes' => 480, 'sort_order' => 2]);
    finishToStart($first, $second);

    app(ProjectScheduler::class)->schedule($project);
    $baseline = app(BaselineService::class)->save($project, 'Inicial');

    $first->update(['duration_minutes' => 1440]);
    app(ProjectScheduler::class)->schedule($project);

    $variances = collect(app(BaselineService::class)->variances($project, $baseline));

    expect($variances->firstWhere('task.id', $first->getKey())['variance_days'])->toEqual(2.0)
        ->and($variances->firstWhere('task.id', $second->getKey())['variance_days'])->toEqual(2.0);
});

it('spreads the planned value of a task across its span', function () {
    $start = CarbonImmutable::parse('2026-01-05 00:00');
    $finish = CarbonImmutable::parse('2026-01-08 00:00');

    expect(BaselineService::plannedValueAt($start, $finish, 900.0, CarbonImmutable::parse('2026-01-05 00:00')))->toBe(0.0)
        ->and(BaselineService::plannedValueAt($start, $finish, 900.0, CarbonImmutable::parse('2026-01-08 00:00')))->toBe(900.0)
        ->and(BaselineService::plannedValueAt($start, $finish, 900.0, CarbonImmutable::parse('2026-01-06 12:00')))->toBe(450.0)
        ->and(BaselineService::plannedValueAt($start, $finish, 0.0, CarbonImmutable::parse('2026-01-06 12:00')))->toBe(0.0)
        ->and(BaselineService::plannedValueAt(null, $finish, 900.0, CarbonImmutable::parse('2026-01-06')))->toBe(0.0);
});
