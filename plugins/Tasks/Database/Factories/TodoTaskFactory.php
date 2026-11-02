<?php

namespace Plugins\Tasks\Database\Factories;

use Plugins\Tasks\Enums\TodoRepeat;
use Plugins\Tasks\Models\TodoList;
use Plugins\Tasks\Models\TodoTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TodoTask>
 */
class TodoTaskFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<TodoTask>
     */
    protected $model = TodoTask::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'todo_list_id' => TodoList::factory(),
            'title' => ucfirst(fake()->words(4, true)),
            'notes' => null,
            'due_at' => null,
            'remind_at' => null,
            'repeat_type' => TodoRepeat::None,
            'is_important' => false,
            'my_day_on' => null,
            'completed_at' => null,
            'position' => 0,
        ];
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the task is important.
     */
    public function important(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_important' => true,
        ]);
    }

    /**
     * Indicate that the task is part of My Day.
     */
    public function myDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'my_day_on' => today()->toDateString(),
        ]);
    }

    /**
     * Indicate that the task is due today.
     */
    public function dueToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => today()->toDateString(),
        ]);
    }

    /**
     * Indicate that the task is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => today()->subDay()->toDateString(),
        ]);
    }

    /**
     * Indicate that the task repeats.
     */
    public function repeats(TodoRepeat $repeat = TodoRepeat::Daily): static
    {
        return $this->state(fn (array $attributes) => [
            'repeat_type' => $repeat,
        ]);
    }
}
