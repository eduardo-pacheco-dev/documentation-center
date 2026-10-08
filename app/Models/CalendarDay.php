<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['calendar_id', 'day_of_week', 'segments'])]
class CalendarDay extends Model
{
    public $timestamps = false;

    /**
     * The calendar the working window belongs to.
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Whether the weekday has any working window.
     */
    public function isWorking(): bool
    {
        return Calendar::totalMinutes($this->segments ?? []) > 0;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'segments' => 'array',
        ];
    }
}
