<?php

namespace App\Services\Agenda;

use App\Enums\AgendaAttendeeStatus;
use App\Models\AgendaEvent;
use App\Models\AgendaEventAttendee;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Persists the related records of an agenda event and applies changes to the
 * individual occurrences of a recurring series.
 */
class AgendaEventSynchronizer
{
    /**
     * Synchronize the guest list, adding the organizer when missing.
     *
     * @param  list<string>  $emails
     * @return list<AgendaEventAttendee> the attendees that were just added
     */
    public function syncAttendees(AgendaEvent $event, array $emails): array
    {
        $event->loadMissing(['attendees', 'user']);

        $organizerEmail = strtolower((string) $event->user?->email);
        $targets = array_values(array_unique(array_filter(array_merge(
            [$organizerEmail],
            array_map(fn (string $email): string => strtolower(trim($email)), $emails),
        ))));

        $existing = $event->attendees->keyBy(fn (AgendaEventAttendee $attendee): string => strtolower($attendee->email));
        $created = [];

        foreach ($targets as $email) {
            $user = User::query()->where('email', $email)->first();
            $isOrganizer = $email === $organizerEmail;
            $attendee = $existing->get($email);

            if ($attendee !== null) {
                $attendee->fill([
                    'user_id' => $user?->getKey(),
                    'name' => $attendee->name ?: $user?->name,
                    'is_organizer' => $isOrganizer,
                ])->save();

                continue;
            }

            $created[] = $event->attendees()->create([
                'user_id' => $user?->getKey(),
                'email' => $email,
                'name' => $user?->name,
                'status' => AgendaAttendeeStatus::NeedsAction,
                'is_organizer' => $isOrganizer,
            ]);
        }

        $event->attendees()->whereNotIn('email', $targets)->delete();

        return $created;
    }

    /**
     * Replace the reminders of the event.
     *
     * @param  list<array{method: string, minutes_before: int|null}>  $reminders
     */
    public function syncReminders(AgendaEvent $event, array $reminders): void
    {
        $event->reminders()->delete();

        foreach ($reminders as $reminder) {
            $event->reminders()->create($reminder);
        }
    }

    /**
     * Store a per-instance override for a recurring event.
     *
     * Passing an empty override list cancels the occurrence.
     *
     * @param  array<string, mixed>  $overrides
     */
    public function applyException(AgendaEvent $event, Carbon $recurrenceId, array $overrides = []): void
    {
        $existing = $event->exceptions()->where('recurrence_id', $recurrenceId)->first();

        $event->exceptions()->updateOrCreate(
            ['recurrence_id' => $recurrenceId],
            [
                'is_cancelled' => $overrides === [],
                'overrides' => $overrides === []
                    ? null
                    : array_merge($existing?->overrides ?? [], $overrides),
            ],
        );
    }

    /**
     * Split a recurring series at the given occurrence, returning the new tail.
     */
    public function splitSeries(AgendaEvent $event, Carbon $recurrenceId, ?Carbon $start = null, ?Carbon $end = null): AgendaEvent
    {
        $originalUntil = $event->recurrence_until?->toDateString();

        $event->update([
            'recurrence_until' => $recurrenceId->copy()->subDay()->toDateString(),
            'sequence' => $event->sequence + 1,
        ]);

        $clone = $event->replicate(['recurrence_until']);
        $clone->start_at = ($start ?? $event->start_at)->copy();
        $clone->end_at = ($end ?? $event->end_at)->copy();
        $clone->recurrence_until = $originalUntil;
        $clone->save();

        foreach ($event->attendees as $attendee) {
            $clone->attendees()->create($attendee->only([
                'user_id', 'email', 'name', 'status', 'is_optional', 'is_organizer', 'is_resource', 'comment', 'responded_at',
            ]));
        }

        foreach ($event->reminders as $reminder) {
            $clone->reminders()->create($reminder->only(['method', 'minutes_before', 'trigger_at']));
        }

        return $clone;
    }
}
