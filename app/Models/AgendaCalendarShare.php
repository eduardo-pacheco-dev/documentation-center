<?php

namespace App\Models;

use App\Enums\AgendaCalendarRole;
use Database\Factories\AgendaCalendarShareFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['agenda_calendar_id', 'user_id', 'role'])]
class AgendaCalendarShare extends Model
{
    /** @use HasFactory<AgendaCalendarShareFactory> */
    use HasFactory;

    /**
     * The shared calendar.
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AgendaCalendar::class, 'agenda_calendar_id');
    }

    /**
     * The user the calendar is shared with.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => AgendaCalendarRole::class,
        ];
    }
}
