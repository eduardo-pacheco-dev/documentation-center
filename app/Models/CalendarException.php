<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['calendar_id', 'date', 'name', 'segments'])]
class CalendarException extends Model
{
    /**
     * The calendar the exception belongs to.
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    /**
     * Whether the exception opens a normally non working day.
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
            'date' => 'date',
            'segments' => 'array',
        ];
    }
}
