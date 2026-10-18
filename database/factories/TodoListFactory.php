<?php

namespace Database\Factories;

use App\Enums\TodoColor;
use App\Enums\TodoIcon;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TodoList>
 */
class TodoListFactory extends Factory
{
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
