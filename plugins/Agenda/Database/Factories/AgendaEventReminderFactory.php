<?php

namespace Plugins\Agenda\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Agenda\Enums\AgendaReminderMethod;
use Plugins\Agenda\Models\AgendaEvent;
use Plugins\Agenda\Models\AgendaEventReminder;

/**
 * @extends Factory<AgendaEventReminder>
 */
class AgendaEventReminderFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<AgendaEventReminder>
     */
    protected $model = AgendaEventReminder::class;

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
