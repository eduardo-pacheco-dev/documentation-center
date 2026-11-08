<?php

namespace Plugins\Agenda\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Agenda\Database\Factories\AgendaEventReminderFactory;
use Plugins\Agenda\Enums\AgendaReminderMethod;

#[Fillable([
    'agenda_event_id',
    'method',
    'minutes_before',
    'trigger_at',
    'sent_at',
])]
class AgendaEventReminder extends Model
{
    /** @use HasFactory<AgendaEventReminderFactory> */
    use HasFactory;

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): AgendaEventReminderFactory
    {
        return AgendaEventReminderFactory::new();
    }

    /**
     * The event the reminder belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(AgendaEvent::class, 'agenda_event_id');
    }

    /**
     * The moment the reminder fires for an occurrence that starts at the given time.
     */
    public function triggerTimeFor(CarbonInterface $start): CarbonInterface
    {
        if ($this->trigger_at !== null) {
            return $this->trigger_at;
        }

        return $start->copy()->subMinutes($this->minutes_before ?? 0);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => AgendaReminderMethod::class,
            'minutes_before' => 'integer',
            'trigger_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
