<?php

namespace Tests\Unit\Scheduling;

use App\Enums\DependencyType;
use App\Models\Calendar;
use App\Models\CalendarDay;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\Scheduling\CircularDependencyException;
use App\Services\Scheduling\CpmEngine;
use App\Services\Scheduling\ScheduleResult;
use App\Services\Scheduling\WorkingCalendar;
use Tests\TestCase;

uses(TestCase::class);

function standardPlan(): WorkingCalendar
{
    $calendar = new Calendar(['name' => 'Padrão', 'is_default' => true]);

    $calendar->setRelation('days', collect(range(1, 7))->map(fn (int $dayOfWeek) => new CalendarDay([
        'day_of_week' => $dayOfWeek,
        'segments' => in_array($dayOfWeek, [1, 2, 3, 4, 5], true) ? [[480, 720], [780, 1020]] : [],
    ])));

    $calendar->setRelation('exceptions', collect());

    return new WorkingCalendar($calendar);
}

function plan(array $attributes = []): Project
{
    $project = new Project;

    return $project->forceFill(array_merge([
        'id' => 1,
        'name' => 'Projeto',
        'start_date' => '2026-01-05',
    ], $attributes));
}

function task(int $id, array $attributes = []): Task
{
    $task = new Task;

    return $task->forceFill(array_merge([
        'id' => $id,
        'project_id' => 1,
        'parent_id' => null,
        'name' => "Tarefa {$id}",
        'duration_minutes' => 480,
        'priority' => 500,
        'percent_complete' => 0,
        'level_delay_minutes' => 0,
        'scheduling_mode' => 'auto',
        'is_milestone' => false,
        'constraint_type' => null,
        'constraint_date' => null,
        'start_at' => null,
        'finish_at' => null,
        'sort_order' => $id,
        'wbs' => (string) $id,
        'outline_level' => 1,
    ], $attributes));
}

function link(int $predecessor, int $successor, DependencyType $type = DependencyType::FinishToStart, int $lag = 0): TaskDependency
{
    $dependency = new TaskDependency;

    return $dependency->forceFill([
        'predecessor_id' => $predecessor,
        'successor_id' => $successor,
        'type' => $type,
        'lag_minutes' => $lag,
    ]);
}

function schedule(array $tasks, array $dependencies = [], ?Project $project = null): ScheduleResult
{
    return (new CpmEngine)->run($project ?? plan(), $tasks, $dependencies, standardPlan());
}

it('schedules a chain of tasks one after the other', function () {
    $result = schedule([task(1), task(2)], [link(1, 2)]);

    expect($result->for(1)->start->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($result->for(1)->finish->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-01-06 17:00')
        ->and($result->finish()->format('Y-m-d H:i'))->toBe('2026-01-06 17:00');
});

it('marks every task of an unbroken chain as critical', function () {
    $result = schedule([task(1), task(2), task(3)], [link(1, 2), link(2, 3)]);

    expect($result->for(1)->critical)->toBeTrue()
        ->and($result->for(2)->critical)->toBeTrue()
        ->and($result->for(3)->critical)->toBeTrue()
        ->and($result->for(1)->totalSlack)->toBe(0);
});

it('gives slack to a task that can finish after the critical path', function () {
    $result = schedule([task(1), task(2), task(3)], [link(1, 2)]);

    expect($result->for(3)->start->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($result->for(3)->critical)->toBeFalse()
        ->and($result->for(3)->totalSlack)->toBe(480)
        ->and($result->for(3)->freeSlack)->toBe(480);
});

it('applies lag to a finish to start dependency', function () {
    $result = schedule([task(1), task(2)], [link(1, 2, DependencyType::FinishToStart, 480)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-07 08:00')
        ->and($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-01-07 17:00');
});

it('moves a task earlier when the lag is negative', function () {
    $result = schedule([task(1), task(2)], [link(1, 2, DependencyType::FinishToStart, -240)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-05 13:00');
});

it('schedules a start to start dependency from the predecessor start', function () {
    $result = schedule([task(1), task(2)], [link(1, 2, DependencyType::StartToStart)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($result->for(1)->critical)->toBeTrue();
});

it('schedules a finish to finish dependency against the predecessor finish', function () {
    $result = schedule([task(1), task(2)], [link(1, 2, DependencyType::FinishToFinish)]);

    expect($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-05 08:00');
});

it('schedules a milestone with no duration at the next working instant', function () {
    $result = schedule([task(1), task(2, ['is_milestone' => true, 'duration_minutes' => 0])], [link(1, 2)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-01-06 08:00')
        ->and($result->for(2)->critical)->toBeTrue();
});

it('keeps a task from starting before a date constraint', function () {
    $result = schedule(
        [task(1), task(2, [
            'constraint_type' => 'start_no_earlier_than',
            'constraint_date' => '2026-01-12 08:00',
        ])],
        [link(1, 2)],
    );

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-12 08:00');
});

it('keeps the dates of a manually scheduled task', function () {
    $result = schedule([
        task(1),
        task(2, [
            'scheduling_mode' => 'manual',
            'start_at' => '2026-03-02 09:00',
            'finish_at' => '2026-03-02 18:00',
        ]),
    ], [link(1, 2)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-03-02 09:00')
        ->and($result->for(2)->finish->format('Y-m-d H:i'))->toBe('2026-03-02 18:00');
});

it('delays a task by its leveling delay', function () {
    $result = schedule([task(1), task(2, ['level_delay_minutes' => 480])], [link(1, 2)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-07 08:00');
});

it('rolls the dates of the children up to the summary task', function () {
    $result = schedule([
        task(10),
        task(1, ['parent_id' => 10]),
        task(2, ['parent_id' => 10]),
    ], [link(1, 2)]);

    expect($result->has(10))->toBeFalse()
        ->and($result->summaryRollups()[10]->start->format('Y-m-d H:i'))->toBe('2026-01-05 08:00')
        ->and($result->summaryRollups()[10]->finish->format('Y-m-d H:i'))->toBe('2026-01-06 17:00');
});

it('resolves a dependency that points at a summary task', function () {
    $result = schedule([
        task(10),
        task(1, ['parent_id' => 10]),
        task(3),
    ], [link(10, 3)]);

    expect($result->for(3)->start->format('Y-m-d H:i'))->toBe('2026-01-06 08:00');
});

it('rejects a plan whose dependencies form a loop', function () {
    expect(fn () => schedule([task(1), task(2)], [link(1, 2), link(2, 1)]))
        ->toThrow(CircularDependencyException::class);
});

it('rejects a task that depends on itself', function () {
    $result = schedule([task(1), task(2)], [link(1, 1), link(1, 2)]);

    expect($result->for(2)->start->format('Y-m-d H:i'))->toBe('2026-01-06 08:00');
});
