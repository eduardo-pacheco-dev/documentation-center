<?php

namespace Plugins\Tasks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Tasks\Models\TodoStep;
use Plugins\Tasks\Models\TodoTask;

/**
 * @extends Factory<TodoStep>
 */
class TodoStepFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<TodoStep>
     */
    protected $model = TodoStep::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'todo_task_id' => TodoTask::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'completed_at' => null,
            'position' => 0,
        ];
    }

    /**
     * Indicate that the step is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }
}
