<?php

namespace Plugins\Tasks\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Tasks\Enums\TodoColor;
use Plugins\Tasks\Enums\TodoIcon;
use Plugins\Tasks\Models\TodoList;

/**
 * @extends Factory<TodoList>
 */
class TodoListFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<TodoList>
     */
    protected $model = TodoList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => ucfirst(fake()->words(2, true)),
            'color' => fake()->randomElement(TodoColor::cases()),
            'icon' => fake()->randomElement(TodoIcon::cases()),
            'position' => 0,
        ];
    }

    /**
     * Indicate the position of the list.
     */
    public function position(int $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }
}
