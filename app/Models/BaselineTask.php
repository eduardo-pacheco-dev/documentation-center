<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'baseline_id',
    'task_id',
    'start_at',
    'finish_at',
    'duration_minutes',
    'work_minutes',
    'budget_cost',
    'percent_complete',
])]
class BaselineTask extends Model
{
    /**
     * The baseline the snapshot belongs to.
     */
    public function baseline(): BelongsTo
    {
        return $this->belongsTo(Baseline::class);
    }

    /**
     * The task the snapshot was taken from.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
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
            'finish_at' => 'datetime',
            'duration_minutes' => 'integer',
            'work_minutes' => 'integer',
            'budget_cost' => 'decimal:2',
            'percent_complete' => 'decimal:2',
        ];
    }
}
