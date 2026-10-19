<?php

namespace App\Models;

use Database\Factories\AgendaEventExceptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'agenda_event_id',
    'recurrence_id',
    'is_cancelled',
    'overrides',
])]
class AgendaEventException extends Model
{
    /** @use HasFactory<AgendaEventExceptionFactory> */
    use HasFactory;

    /**
     * The recurring event this exception belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(AgendaEvent::class, 'agenda_event_id');
    }

    /**
     * The start of the occurrence this exception replaces.
     */
    public function recurrenceStart(): Carbon
    {
        return $this->recurrence_id->copy();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recurrence_id' => 'datetime',
            'is_cancelled' => 'boolean',
            'overrides' => 'array',
        ];
    }
}
