<?php

namespace Database\Factories;

use App\Enums\AgendaReminderMethod;
use App\Models\AgendaEvent;
use App\Models\AgendaEventReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaEventReminder>
 */
class AgendaEventReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agenda_event_id' => AgendaEvent::factory(),
            'method' => AgendaReminderMethod::Popup,
            'minutes_before' => 10,
            'trigger_at' => null,
            'sent_at' => null,
        ];
    }

    /**
     * Indicate that the reminder is delivered by e-mail.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => AgendaReminderMethod::Email,
        ]);
    }
}
