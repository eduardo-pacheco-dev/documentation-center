<?php

namespace Database\Factories;

use App\Enums\AgendaAttendeeStatus;
use App\Models\AgendaEvent;
use App\Models\AgendaEventAttendee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgendaEventAttendee>
 */
class AgendaEventAttendeeFactory extends Factory
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
            'user_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => AgendaAttendeeStatus::NeedsAction,
            'is_optional' => false,
            'is_organizer' => false,
            'is_resource' => false,
            'comment' => null,
            'responded_at' => null,
        ];
    }

    /**
     * Invite an application user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->getKey(),
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    /**
     * Indicate that the attendee accepted the invitation.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AgendaAttendeeStatus::Accepted,
            'responded_at' => now(),
        ]);
    }
}
