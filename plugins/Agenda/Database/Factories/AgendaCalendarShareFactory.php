<?php

namespace Plugins\Agenda\Database\Factories;

use Plugins\Agenda\Enums\AgendaCalendarRole;
use Plugins\Agenda\Models\AgendaCalendar;
use Plugins\Agenda\Models\AgendaCalendarShare;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaCalendarShare>
 */
class AgendaCalendarShareFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<AgendaCalendarShare>
     */
    protected $model = AgendaCalendarShare::class;

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
