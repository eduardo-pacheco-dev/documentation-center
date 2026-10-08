<?php

namespace Database\Factories;

use App\Models\Baseline;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Baseline>
 */
class BaselineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
            'name' => 'Baseline '.fake()->numberBetween(1, 99),
            'saved_at' => now(),
        ];
    }

    /**
     * Freeze a copy of every task of the project.
     */
    public function withTasks(): static
    {
        return $this->afterCreating(function (Baseline $baseline): void {
            $baseline->project->tasks()->get()->each(function ($task) use ($baseline): void {
                $baseline->tasks()->create([
                    'task_id' => $task->getKey(),
                    'start_at' => $task->start_at,
                    'finish_at' => $task->finish_at,
                    'duration_minutes' => $task->duration_minutes,
                    'work_minutes' => $task->work_minutes,
                    'budget_cost' => $task->budget_cost,
                    'percent_complete' => $task->percent_complete,
                ]);
            });
        });
    }
}
