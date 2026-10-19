<?php

namespace Database\Factories;

use App\Enums\AgendaColor;
use App\Models\AgendaCalendar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaCalendar>
 */
class AgendaCalendarFactory extends Factory
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
            'description' => null,
            'color' => AgendaColor::Blueberry,
            'timezone' => 'UTC',
            'is_default' => false,
            'is_visible' => true,
            'position' => 0,
            'default_reminders' => [
                ['method' => 'popup', 'minutes_before' => 10],
            ],
        ];
    }

    /**
     * Indicate that the calendar is the user's default one.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the calendar is hidden from the grid.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }
}
