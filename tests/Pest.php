<?php

use App\Enums\DependencyType;
use App\Models\Project;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may sometimes need some testing code specific
| to your project that you don't want to repeat in many test files. Here you can also expose
| helpers as global functions to help you to reduce the number of lines of code in your tests.
|
*/

function something()
{
    // ..
}

/**
 * A project that starts on Monday 2026-01-05 with a standard working calendar.
 */
function workingProject(string $startDate = '2026-01-05'): Project
{
    return Project::factory()->withCalendar()->create(['start_date' => $startDate]);
}

/**
 * Link two tasks with a finish to start dependency.
 */
function finishToStart(Task $predecessor, Task $successor, int $lag = 0): TaskDependency
{
    return TaskDependency::query()->create([
        'project_id' => $predecessor->project_id,
        'predecessor_id' => $predecessor->getKey(),
        'successor_id' => $successor->getKey(),
        'type' => DependencyType::FinishToStart,
        'lag_minutes' => $lag,
    ]);
}

/**
 * Assign a resource to a task for its whole duration.
 */
function assign(Resource $resource, Task $task, float $units = 100): ResourceAssignment
{
    return ResourceAssignment::query()->create([
        'resource_id' => $resource->getKey(),
        'task_id' => $task->getKey(),
        'units' => $units,
        'work_minutes' => $task->duration_minutes,
        'cost' => 0,
    ]);
}
