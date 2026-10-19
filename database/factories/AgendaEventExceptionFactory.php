<?php

namespace Database\Factories;

use App\Models\AgendaEvent;
use App\Models\AgendaEventException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaEventException>
 */
class AgendaEventExceptionFactory extends Factory
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
            'recurrence_id' => now()->addDay()->startOfHour(),
            'is_cancelled' => false,
            'overrides' => null,
        ];
    }

    /**
     * Indicate that the occurrence was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cancelled' => true,
        ]);
    }
}
