<?php

namespace App\Models;

use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaEventVisibility;
use App\Enums\AgendaTransparency;
use Carbon\CarbonInterface;
use Database\Factories\AgendaEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'agenda_calendar_id',
    'user_id',
    'title',
    'description',
    'location',
    'meeting_url',
    'start_at',
    'end_at',
    'is_all_day',
    'timezone',
    'status',
    'visibility',
    'transparency',
    'color',
    'recurrence',
    'recurrence_until',
    'attendees_can_invite_others',
    'attendees_can_modify',
    'attendees_can_see_other_attendees',
    'attachments',
    'sequence',
])]
class AgendaEvent extends Model
{
    /** @use HasFactory<AgendaEventFactory> */
    use HasFactory;

    /**
     * The calendar the event belongs to.
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AgendaCalendar::class, 'agenda_calendar_id');
    }

    /**
     * The organizer of the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The guests invited to the event.
     *
     * @return HasMany<AgendaEventAttendee, $this>
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(AgendaEventAttendee::class);
    }

    /**
     * The reminders configured for the event.
     *
     * @return HasMany<AgendaEventReminder, $this>
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(AgendaEventReminder::class);
    }

    /**
     * The per-instance overrides and cancellations of a recurring event.
     *
     * @return HasMany<AgendaEventException, $this>
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(AgendaEventException::class);
    }

    /**
     * Determine whether the event repeats.
     */
    public function isRecurring(): bool
    {
        return is_array($this->recurrence) && isset($this->recurrence['frequency']);
    }

    /**
     * The duration of the event in minutes.
     */
    public function durationInMinutes(): int
    {
        return max(1, (int) round(($this->end_at->getTimestamp() - $this->start_at->getTimestamp()) / 60));
    }

    /**
     * Determine whether the event spans more than a single day.
     */
    public function spansMultipleDays(): bool
    {
        return ! $this->start_at->isSameDay($this->end_at);
    }

    /**
     * The color used to render the event, falling back to the calendar color.
     */
    public function resolvedColor(): AgendaColor
    {
        return $this->color
            ?? $this->calendar->color
            ?? AgendaColor::Blueberry;
    }

    /**
     * The attendee record linked to the given user, when present.
     */
    public function attendeeFor(User $user): ?AgendaEventAttendee
    {
        return $this->attendees->first(
            fn (AgendaEventAttendee $attendee): bool => $attendee->user_id === $user->getKey(),
        );
    }

    /**
     * Determine whether the user organized the event.
     */
    public function isOrganizer(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    /**
     * The normalized attachments of the event.
     *
     * @return list<array{title: string, url: string, mime_type: string|null}>
     */
    public function attachmentList(): array
    {
        return array_values($this->attachments ?? []);
    }

    /**
     * Limit the query to the events that overlap the given window.
     *
     * Recurring events are matched on their recurrence window because the
     * individual occurrences are expanded afterwards.
     *
     * @param  Builder<AgendaEvent>  $query
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $from, CarbonInterface $to): void
    {
        $query->where(function (Builder $query) use ($from, $to): void {
            $query->where(function (Builder $query) use ($from, $to): void {
                $query->whereNull('recurrence')
                    ->where('start_at', '<=', $to)
                    ->where('end_at', '>=', $from);
            })->orWhere(function (Builder $query) use ($from, $to): void {
                $query->whereNotNull('recurrence')
                    ->where('start_at', '<=', $to)
                    ->where(function (Builder $query) use ($from): void {
                        $query->whereNull('recurrence_until')
                            ->orWhere('recurrence_until', '>=', $from->toDateString());
                    });
            });
        });
    }

    /**
     * Determine whether the event blocks the given window in free/busy views.
     */
    public function blocks(CarbonInterface $from, CarbonInterface $to): bool
    {
        return $this->transparency->blocksTime()
            && $this->status !== AgendaEventStatus::Cancelled
            && $this->start_at->lessThan($to)
            && $this->end_at->greaterThan($from);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_all_day' => 'boolean',
            'status' => AgendaEventStatus::class,
            'visibility' => AgendaEventVisibility::class,
            'transparency' => AgendaTransparency::class,
            'color' => AgendaColor::class,
            'recurrence' => 'array',
            'recurrence_until' => 'date',
            'attendees_can_invite_others' => 'boolean',
            'attendees_can_modify' => 'boolean',
            'attendees_can_see_other_attendees' => 'boolean',
            'attachments' => 'array',
            'sequence' => 'integer',
        ];
    }
}
