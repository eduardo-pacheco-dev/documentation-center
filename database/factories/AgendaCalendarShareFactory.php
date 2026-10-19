<?php

namespace Database\Factories;

use App\Enums\AgendaCalendarRole;
use App\Models\AgendaCalendar;
use App\Models\AgendaCalendarShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaCalendarShare>
 */
class AgendaCalendarShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agenda_calendar_id' => AgendaCalendar::factory(),
            'user_id' => User::factory(),
            'role' => AgendaCalendarRole::Reader,
        ];
    }

    /**
     * Indicate that the share grants write access.
     */
    public function writer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AgendaCalendarRole::Writer,
        ]);
    }
}
