<?php

namespace Database\Factories;

use App\Enums\AgendaEventStatus;
use App\Enums\AgendaEventVisibility;
use App\Enums\AgendaRecurrenceFrequency;
use App\Enums\AgendaTransparency;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<AgendaEvent>
 */
class AgendaEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = Carbon::now()->addDay()->setTime(9, 0);

        return [
            'agenda_calendar_id' => AgendaCalendar::factory(),
            'user_id' => User::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'description' => null,
            'location' => null,
            'meeting_url' => null,
            'start_at' => $start,
            'end_at' => $start->copy()->addHour(),
            'is_all_day' => false,
            'timezone' => 'UTC',
            'status' => AgendaEventStatus::Confirmed,
            'visibility' => AgendaEventVisibility::Default,
            'transparency' => AgendaTransparency::Opaque,
            'color' => null,
            'recurrence' => null,
            'recurrence_until' => null,
            'attendees_can_invite_others' => true,
            'attendees_can_modify' => false,
            'attendees_can_see_other_attendees' => true,
            'attachments' => null,
            'sequence' => 0,
        ];
    }

    /**
     * Place the event on the given calendar, owned by its user.
     */
    public function forCalendar(AgendaCalendar $calendar): static
    {
        return $this->state(fn (array $attributes) => [
            'agenda_calendar_id' => $calendar->getKey(),
            'user_id' => $calendar->user_id,
        ]);
    }

    /**
     * Indicate that the event lasts the whole day.
     */
    public function allDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => Carbon::now()->addDay()->startOfDay(),
            'end_at' => Carbon::now()->addDays(2)->startOfDay(),
            'is_all_day' => true,
        ]);
    }

    /**
     * Indicate that the event repeats.
     */
    public function recurring(
        AgendaRecurrenceFrequency $frequency = AgendaRecurrenceFrequency::Weekly,
        int $interval = 1,
    ): static {
        return $this->state(fn (array $attributes) => [
            'recurrence' => [
                'frequency' => $frequency->value,
                'interval' => $interval,
            ],
        ]);
    }

    /**
     * Indicate that the event was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgendaEventStatus::Cancelled,
        ]);
    }

    /**
     * Place the event on a specific day at a specific hour.
     */
    public function on(Carbon $start): static
    {
        return $this->state(fn (array $attributes) => [
            'start_at' => $start->copy(),
            'end_at' => $start->copy()->addHour(),
        ]);
    }
}
