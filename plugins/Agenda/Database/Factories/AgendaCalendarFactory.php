<?php

namespace Plugins\Agenda\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Agenda\Enums\AgendaColor;
use Plugins\Agenda\Models\AgendaCalendar;

/**
 * @extends Factory<AgendaCalendar>
 */
class AgendaCalendarFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<AgendaCalendar>
     */
    protected $model = AgendaCalendar::class;

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
