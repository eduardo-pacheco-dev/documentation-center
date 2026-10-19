<?php

namespace App\Services\Agenda;

use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaTransparency;
use App\Models\AgendaEvent;
use Carbon\CarbonInterface;

/**
 * A single rendered instance of an agenda event.
 *
 * Recurring events produce one occurrence per matched date, while the
 * `overrides` array carries the changes applied by a recurrence exception.
 */
final readonly class AgendaOccurrence
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    public function __construct(
        public AgendaEvent $event,
        public CarbonInterface $start,
        public CarbonInterface $end,
        public bool $isRecurring,
        public ?CarbonInterface $recurrenceId = null,
        public array $overrides = [],
    ) {}

    /**
     * The title shown for the occurrence.
     */
    public function title(): string
    {
        return (string) ($this->overrides['title'] ?? $this->event->title);
    }

    /**
     * The status of the occurrence.
     */
    public function status(): AgendaEventStatus
    {
        return $this->enumOverride('status', AgendaEventStatus::class) ?? $this->event->status;
    }

    /**
     * The color of the occurrence.
     */
    public function color(): AgendaColor
    {
        return $this->enumOverride('color', AgendaColor::class) ?? $this->event->resolvedColor();
    }

    /**
     * The transparency of the occurrence.
     */
    public function transparency(): AgendaTransparency
    {
        return $this->enumOverride('transparency', AgendaTransparency::class) ?? $this->event->transparency;
    }

    /**
     * The location of the occurrence.
     */
    public function location(): ?string
    {
        return $this->stringOverride('location') ?? $this->event->location;
    }

    /**
     * The meeting URL of the occurrence.
     */
    public function meetingUrl(): ?string
    {
        return $this->stringOverride('meeting_url') ?? $this->event->meeting_url;
    }

    /**
     * The description of the occurrence.
     */
    public function description(): ?string
    {
        return $this->stringOverride('description') ?? $this->event->description;
    }

    /**
     * Determine whether the occurrence lasts the whole day.
     */
    public function isAllDay(): bool
    {
        return (bool) ($this->overrides['is_all_day'] ?? $this->event->is_all_day);
    }

    /**
     * Determine whether the occurrence was cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status() === AgendaEventStatus::Cancelled;
    }

    /**
     * The duration of the occurrence in minutes.
     */
    public function durationInMinutes(): int
    {
        return max(1, (int) round(($this->end->getTimestamp() - $this->start->getTimestamp()) / 60));
    }

    /**
     * The payload used to populate the event editor modal.
     *
     * @return array<string, mixed>
     */
    public function toModalPayload(): array
    {
        $event = $this->event;
        $user = auth()->user();
        $colorOverride = $this->overrides['color'] ?? null;
        $color = $colorOverride instanceof \BackedEnum ? $colorOverride->value : ($colorOverride ?? $event->color?->value);
        $attendee = $user !== null ? $event->attendeeFor($user) : null;

        return [
            'id' => $event->getKey(),
            'title' => $this->title(),
            'description' => $this->description(),
            'location' => $this->location(),
            'meeting_url' => $this->meetingUrl(),
            'calendar_id' => $event->agenda_calendar_id,
            'start' => $this->start->format('Y-m-d\TH:i'),
            'end' => $this->end->format('Y-m-d\TH:i'),
            'is_all_day' => $this->isAllDay(),
            'status' => $this->status()->value,
            'visibility' => $this->overrides['visibility'] ?? $event->visibility->value,
            'transparency' => $this->transparency()->value,
            'color' => $color,
            'is_recurring' => $this->isRecurring,
            'recurrence_id' => $this->recurrenceId?->format('Y-m-d\TH:i'),
            'recurrence' => $event->recurrence,
            'recurrence_until' => $event->recurrence_until?->format('Y-m-d'),
            'attendees_can_invite_others' => (bool) $event->attendees_can_invite_others,
            'attendees_can_modify' => (bool) $event->attendees_can_modify,
            'attendees_can_see_other_attendees' => (bool) $event->attendees_can_see_other_attendees,
            'attendees' => $event->attendees
                ->map(fn ($item): array => [
                    'email' => $item->email,
                    'name' => $item->name,
                    'status' => $item->status->value,
                    'is_optional' => (bool) $item->is_optional,
                    'is_organizer' => (bool) $item->is_organizer,
                ])->values()->all(),
            'reminders' => $event->reminders
                ->map(fn ($item): array => [
                    'method' => $item->method->value,
                    'minutes_before' => $item->minutes_before,
                ])->values()->all(),
            'attachments' => $event->attachmentList(),
            'organizer' => $event->user?->full_name,
            'can_update' => $user !== null && ($event->calendar->canWrite($user) || $event->isOrganizer($user)),
            'can_delete' => $user !== null && ($event->calendar->canWrite($user) || $event->isOrganizer($user)),
            'is_organizer' => $user !== null && $event->isOrganizer($user),
            'attendee_status' => $attendee?->status->value,
            'can_respond' => $attendee !== null,
        ];
    }

    /**
     * Resolve an enum override coming from a raw string or an enum instance.
     *
     * @param  class-string<AgendaEventStatus|AgendaColor|AgendaTransparency>  $enum
     */
    private function enumOverride(string $key, string $enum): ?object
    {
        $value = $this->overrides[$key] ?? null;

        if ($value === null) {
            return null;
        }

        return $value instanceof $enum ? $value : $enum::from($value);
    }

    /**
     * Resolve a nullable string override.
     */
    private function stringOverride(string $key): ?string
    {
        return isset($this->overrides[$key]) ? (string) $this->overrides[$key] : null;
    }
}
