<?php

use App\Models\Project;
use App\Models\Task;
use App\Services\Baseline\BaselineService;
use App\Services\EarnedValue\EarnedValueService;
use App\Services\Scheduling\ProjectScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Two one day tasks of 1000 each, both planned on Monday 2026-01-05.
 */
function budgetedPlan(float $firstCost, float $firstPercent, float $firstActual, float $secondCost): Project
{
    $project = workingProject();

    Task::factory()->for($project)->create([
        'name' => 'Escopo A',
        'duration_minutes' => 480,
        'budget_cost' => $firstCost,
        'percent_complete' => $firstPercent,
        'actual_cost' => $firstActual,
        'actual_start_at' => $firstPercent > 0 ? '2026-01-05 08:00' : null,
        'sort_order' => 1,
    ]);

    Task::factory()->for($project)->create([
        'name' => 'Escopo B',
        'duration_minutes' => 480,
        'budget_cost' => $secondCost,
        'percent_complete' => 0,
        'actual_cost' => 0,
        'sort_order' => 2,
    ]);

    app(ProjectScheduler::class)->schedule($project);

    return $project;
}

it('calculates the earned value indicators of a project', function () {
    $project = budgetedPlan(1000, 100, 800, 1000);

    $report = app(EarnedValueService::class)->report($project, '2026-01-05');

    expect($report['bac'])->toBe(2000.0)
        ->and($report['pv'])->toBe(2000.0)
        ->and($report['ev'])->toBe(1000.0)
        ->and($report['ac'])->toBe(800.0)
        ->and($report['cv'])->toBe(200.0)
        ->and($report['sv'])->toBe(-1000.0)
        ->and($report['cpi'])->toBe(1.25)
        ->and($report['spi'])->toBe(0.5)
        ->and($report['eac'])->toBe(1600.0)
        ->and($report['etc'])->toBe(800.0)
        ->and($report['vac'])->toBe(400.0)
        ->and($report['tcpi'])->toBe(0.83)
        ->and($report['planned_percent'])->toBe(100.0)
        ->and($report['earned_percent'])->toBe(50.0)
        ->and($report['status_date'])->toBe('2026-01-05');
});

it('counts only the leaf tasks in the budget', function () {
    $project = workingProject();
    $summary = Task::factory()->summary()->for($project)->create(['budget_cost' => 5000, 'sort_order' => 1]);
    Task::factory()->for($project)->create([
        'parent_id' => $summary->getKey(),
        'budget_cost' => 1000,
        'duration_minutes' => 480,
        'sort_order' => 1,
    ]);
    Task::factory()->for($project)->create([
        'parent_id' => $summary->getKey(),
        'budget_cost' => 1000,
        'duration_minutes' => 480,
        'sort_order' => 2,
    ]);
    app(ProjectScheduler::class)->schedule($project);

    $report = app(EarnedValueService::class)->report($project, '2026-01-05');

    expect($report['bac'])->toBe(2000.0)
        ->and($report['pv'])->toBe(2000.0);
});

it('falls back to the live plan when no baseline was saved', function () {
    $project = budgetedPlan(1000, 0, 0, 0);

    $report = app(EarnedValueService::class)->report($project, '2026-01-05');

    expect($report['baseline'])->toBeNull()
        ->and($report['pv'])->toBe(1000.0)
        ->and($report['spi'])->toBe(0.0)
        ->and($report['cpi'])->toBeNull();
});

it('reads the planned value from a saved baseline', function () {
    $project = budgetedPlan(1000, 0, 0, 1000);
    app(BaselineService::class)->save($project, 'Inicial');

    Task::query()->whereBelongsTo($project)->update(['duration_minutes' => 960]);
    app(ProjectScheduler::class)->schedule($project);

    $report = app(EarnedValueService::class)->report($project, '2026-01-08');

    expect($report['baseline']['name'])->toBe('Inicial')
        ->and($report['pv'])->toBe(2000.0);
});

it('builds the S curve of the project', function () {
    $project = budgetedPlan(1000, 100, 800, 1000);

    $report = app(EarnedValueService::class)->report($project, '2026-01-06');
    $series = $report['series'];

    expect($series)->not->toBeEmpty()
        ->and($series[0]['date'])->toBe('2026-01-05')
        ->and($series[0]['pv'])->toBe(2000.0)
        ->and($series[0]['ev'])->toBe(1000.0)
        ->and($series[0]['ac'])->toBe(800.0)
        ->and(collect($series)->last()['pv'])->toBe(2000.0);
});
