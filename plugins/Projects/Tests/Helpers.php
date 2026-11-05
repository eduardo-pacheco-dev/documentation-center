<?php

use Plugins\Projects\Enums\CalendarType;
use Plugins\Projects\Enums\DependencyType;
use Plugins\Projects\Models\Calendar;
use Plugins\Projects\Models\Project;
use Plugins\Projects\Models\Resource;
use Plugins\Projects\Models\ResourceAssignment;
use Plugins\Projects\Models\Task;
use Plugins\Projects\Models\TaskDependency;

/**
 * Create a project with a standard working calendar that starts on Monday,
 * 2026-01-05, which is the reference date used by the plugin tests.
 */
function workingProject(): Project
{
    $project = Project::factory()->create(['start_date' => '2026-01-05']);

    if (! $project->calendars()->where('is_default', true)->exists()) {
        Calendar::createDefault($project, CalendarType::Standard, 'Calendário padrão');
    }

    return $project;
}

/**
 * Link two tasks with a finish-to-start dependency without lag.
 */
function finishToStart(Task $predecessor, Task $successor): TaskDependency
{
    return TaskDependency::query()->create([
        'project_id' => $predecessor->project_id,
        'predecessor_id' => $predecessor->getKey(),
        'successor_id' => $successor->getKey(),
        'type' => DependencyType::FinishToStart,
        'lag_minutes' => 0,
    ]);
}

/**
 * Allocate the whole capacity of a resource to a task.
 */
function assign(Resource $resource, Task $task, float $units = 100): ResourceAssignment
{
    return ResourceAssignment::query()->create([
        'task_id' => $task->getKey(),
        'resource_id' => $resource->getKey(),
        'units' => $units,
        'work_minutes' => $task->duration_minutes,
        'cost' => 0,
    ]);
}
