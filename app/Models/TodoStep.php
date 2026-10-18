<?php

namespace App\Models;

use Database\Factories\TodoStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['todo_task_id', 'title', 'completed_at', 'position'])]
class TodoStep extends Model
{
    /** @use HasFactory<TodoStepFactory> */
    use HasFactory;

    /**
     * The task the step belongs to.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(TodoTask::class, 'todo_task_id');
    }

    /**
     * Determine whether the step is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
