<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
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
            'parent_id' => null,
            'name' => fake()->words(3, true),
            'wbs' => '1',
            'outline_level' => 1,
            'sort_order' => fake()->numberBetween(1, 1000),
            'task_type' => 'fixed_duration',
            'scheduling_mode' => 'auto',
            'is_milestone' => false,
            'start_at' => null,
            'finish_at' => null,
            'duration_minutes' => 480,
            'work_minutes' => null,
            'constraint_type' => null,
            'constraint_date' => null,
            'priority' => 500,
            'percent_complete' => 0,
            'actual_start_at' => null,
            'actual_finish_at' => null,
            'actual_duration_minutes' => null,
            'actual_cost' => 0,
            'budget_cost' => 0,
            'level_delay_minutes' => 0,
            'notes' => null,
        ];
    }

    /**
     * Turn the task into a summary task.
     */
    public function summary(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_type' => 'fixed_duration',
            'duration_minutes' => null,
        ]);
    }

    /**
     * Turn the task into a milestone.
     */
    public function milestone(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_milestone' => true,
            'duration_minutes' => 0,
        ]);
    }

    /**
     * Give the task already calculated dates.
     */
    public function scheduled(string $startAt, string $finishAt): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $startAt,
            'finish_at' => $finishAt,
        ]);
    }

    /**
     * Record progress on the task.
     */
    public function inProgress(int $percentComplete, string $actualStartAt): static
    {
        return $this->state(fn (array $attributes) => [
            'percent_complete' => $percentComplete,
            'actual_start_at' => $actualStartAt,
        ]);
    }
}
