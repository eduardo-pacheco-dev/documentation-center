<?php

namespace Database\Factories;

use App\Enums\DependencyType;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskDependency>
 */
class TaskDependencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Tests should point both tasks at the same project.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'predecessor_id' => Task::factory(),
            'successor_id' => Task::factory(),
            'type' => DependencyType::FinishToStart,
            'lag_minutes' => 0,
        ];
    }

    /**
     * Link the tasks start to start.
     */
    public function startToStart(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => DependencyType::StartToStart,
        ]);
    }

    /**
     * Delay the successor by the given working minutes.
     */
    public function withLag(int $lagMinutes): static
    {
        return $this->state(fn (array $attributes) => [
            'lag_minutes' => $lagMinutes,
        ]);
    }
}
