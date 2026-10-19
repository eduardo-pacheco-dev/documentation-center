<?php

namespace App\Models;

use App\Enums\AgendaAttendeeStatus;
use Database\Factories\AgendaEventAttendeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'agenda_event_id',
    'user_id',
    'email',
    'name',
    'status',
    'is_optional',
    'is_organizer',
    'is_resource',
    'comment',
    'responded_at',
])]
class AgendaEventAttendee extends Model
{
    /** @use HasFactory<AgendaEventAttendeeFactory> */
    use HasFactory;

    /**
     * The event the attendee is invited to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(AgendaEvent::class, 'agenda_event_id');
    }

    /**
     * The application user behind the attendee, when the e-mail is known.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine whether the attendee has answered the invitation.
     */
    public function hasResponded(): bool
    {
        return $this->status !== AgendaAttendeeStatus::NeedsAction;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AgendaAttendeeStatus::class,
            'is_optional' => 'boolean',
            'is_organizer' => 'boolean',
            'is_resource' => 'boolean',
            'responded_at' => 'datetime',
        ];
    }
}
