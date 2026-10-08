<?php

use App\Models\Resource;
use App\Models\Task;
use App\Services\Scheduling\ProjectScheduler;
use App\Services\Scheduling\ResourceLeveler;
use App\Services\Scheduling\WbsCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists the calculated dates of a project', function () {
    $project = workingProject();
    $first = Task::factory()->for($project)->create(['name' => 'Fundação', 'duration_minutes' => 480, 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['name' => 'Alvenaria', 'duration_minutes' => 480, 'sort_order' => 2]);
    finishToStart($first, $second);

    app(ProjectScheduler::class)->schedule($project);

    $first->refresh();
    $second->refresh();
    $project->refresh();

    expect($first->start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($first->finish_at->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($first->critical)->toBeTrue()
        ->and($second->start_at->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($second->finish_at->format('Y-m-d H:i'))->toBe('2026-01-06 17:00')
        ->and($second->critical)->toBeTrue()
        ->and($project->start_date->toDateString())->toBe('2026-01-05')
        ->and($project->finish_date->toDateString())->toBe('2026-01-06')
        ->and($project->scheduled_at)->not->toBeNull();
});

it('marks tasks outside the critical path with slack', function () {
    $project = workingProject();
    $first = Task::factory()->for($project)->create(['sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['sort_order' => 2]);
    $loose = Task::factory()->for($project)->create(['sort_order' => 3]);
    finishToStart($first, $second);

    app(ProjectScheduler::class)->schedule($project);

    $loose->refresh();

    expect($loose->critical)->toBeFalse()
        ->and($loose->total_slack_minutes)->toBe(480)
        ->and($loose->free_slack_minutes)->toBe(480);
});

it('rolls child progress and cost up to the summary task', function () {
    $project = workingProject();
    $summary = Task::factory()->summary()->for($project)->create(['sort_order' => 1]);
    $done = Task::factory()->for($project)->create([
        'parent_id' => $summary->getKey(),
        'sort_order' => 1,
        'duration_minutes' => 480,
        'percent_complete' => 100,
        'budget_cost' => 100,
    ]);
    $pending = Task::factory()->for($project)->create([
        'parent_id' => $summary->getKey(),
        'sort_order' => 2,
        'duration_minutes' => 480,
        'percent_complete' => 0,
        'budget_cost' => 200,
    ]);

    app(ProjectScheduler::class)->schedule($project);

    $summary->refresh();
    $project->refresh();

    expect($summary->percent_complete)->toEqual(50.0)
        ->and($summary->budget_cost)->toEqual(300.0)
        ->and($summary->start_at->toDateTimeString())->toBe($done->refresh()->start_at->toDateTimeString())
        ->and($summary->finish_at->toDateTimeString())->toBe($pending->refresh()->finish_at->toDateTimeString())
        ->and($project->percent_complete)->toEqual(50.0);
});

it('records the actuals of a finished task and clamps the progress', function () {
    $project = workingProject();
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);

    app(ProjectScheduler::class)->applyProgress(
        $project,
        $task,
        150,
        CarbonImmutable::parse('2026-01-05 08:00'),
        CarbonImmutable::parse('2026-01-05 17:00'),
    );

    $task->refresh();

    expect($task->percent_complete)->toEqual(100.0)
        ->and($task->actual_start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($task->actual_finish_at->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($task->actual_duration_minutes)->toBe(480);
});

it('keeps a task in progress without an actual finish', function () {
    $project = workingProject();
    $task = Task::factory()->for($project)->create(['duration_minutes' => 480]);

    app(ProjectScheduler::class)->applyProgress($project, $task, 40, null, null);

    $task->refresh();

    expect($task->percent_complete)->toEqual(40.0)
        ->and($task->actual_start_at)->not->toBeNull()
        ->and($task->actual_finish_at)->toBeNull()
        ->and($task->actual_duration_minutes)->toBeNull();
});

it('delays a task to remove a resource overallocation', function () {
    $project = workingProject();
    $first = Task::factory()->for($project)->create(['name' => 'Pintura A', 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['name' => 'Pintura B', 'sort_order' => 2]);
    $resource = Resource::factory()->for($project)->create(['max_units' => 100]);
    assign($resource, $first);
    assign($resource, $second);

    $outcome = app(ResourceLeveler::class)->level($project);

    $first->refresh();
    $second->refresh();

    expect($outcome['shifted'])->toBe(1)
        ->and($outcome['remaining'])->toBe(0)
        ->and($first->start_at->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($second->start_at->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($second->level_delay_minutes)->toBe(480)
        ->and($first->level_delay_minutes)->toBe(0);
});

it('never delays a task whose priority protects it', function () {
    $project = workingProject();
    $protected = Task::factory()->for($project)->create(['name' => 'Crítica', 'priority' => 1000, 'sort_order' => 1]);
    $flexible = Task::factory()->for($project)->create(['name' => 'Flexible', 'priority' => 500, 'sort_order' => 2]);
    $resource = Resource::factory()->for($project)->create(['max_units' => 100]);
    assign($resource, $protected);
    assign($resource, $flexible);

    $outcome = app(ResourceLeveler::class)->level($project);

    expect($outcome['shifted'])->toBe(1)
        ->and($protected->refresh()->level_delay_minutes)->toBe(0)
        ->and($flexible->refresh()->level_delay_minutes)->toBe(480);
});

it('reports a conflict that cannot be resolved', function () {
    $project = workingProject();
    $first = Task::factory()->for($project)->create(['priority' => 1000, 'sort_order' => 1]);
    $second = Task::factory()->for($project)->create(['priority' => 1000, 'sort_order' => 2]);
    $resource = Resource::factory()->for($project)->create(['max_units' => 100]);
    assign($resource, $first);
    assign($resource, $second);

    $outcome = app(ResourceLeveler::class)->level($project);

    expect($outcome['shifted'])->toBe(0)
        ->and($outcome['remaining'])->toBeGreaterThan(0)
        ->and($first->refresh()->level_delay_minutes)->toBe(0)
        ->and($second->refresh()->level_delay_minutes)->toBe(0);
});

it('numbers the work breakdown structure from the outline', function () {
    $project = workingProject();
    $first = Task::factory()->summary()->for($project)->create(['sort_order' => 1]);
    Task::factory()->for($project)->create(['parent_id' => $first->getKey(), 'sort_order' => 1]);
    Task::factory()->for($project)->create(['parent_id' => $first->getKey(), 'sort_order' => 2]);
    $second = Task::factory()->summary()->for($project)->create(['sort_order' => 2]);

    app(WbsCalculator::class)->recalculate($project);

    expect($first->refresh()->wbs)->toBe('1')
        ->and($first->outline_level)->toBe(1)
        ->and($second->refresh()->wbs)->toBe('2')
        ->and($project->tasks()->where('parent_id', $first->getKey())->orderBy('sort_order')->pluck('wbs')->all())
        ->toBe(['1.1', '1.2'])
        ->and($project->tasks()->where('parent_id', $first->getKey())->pluck('outline_level')->unique()->all())
        ->toBe([2]);
});
