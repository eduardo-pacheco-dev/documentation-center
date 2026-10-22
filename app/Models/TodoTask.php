<?php

namespace App\Models;

use App\Enums\TodoRepeat;
use Carbon\CarbonInterface;
use Database\Factories\TodoTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'todo_list_id',
    'agenda_event_id',
    'title',
    'notes',
    'due_at',
    'remind_at',
    'repeat_type',
    'is_important',
    'my_day_on',
    'completed_at',
    'position',
])]
class TodoTask extends Model
{
    /** @use HasFactory<TodoTaskFactory> */
    use HasFactory;

    /**
     * The user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The list the task belongs to.
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /**
     * The agenda event linked to the task.
     */
    public function agendaEvent(): BelongsTo
    {
        return $this->belongsTo(AgendaEvent::class);
    }

    /**
     * The checklist steps of the task.
     *
     * @return HasMany<TodoStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(TodoStep::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Determine whether the task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Determine whether the task is due today.
     */
    public function isDueToday(): bool
    {
        return $this->due_at !== null && $this->due_at->isToday();
    }

    /**
     * Determine whether the task is past its due date.
     */
    public function isOverdue(): bool
    {
        return ! $this->isCompleted()
            && $this->due_at !== null
            && $this->due_at->isBefore(today());
    }

    /**
     * Determine whether the task is scheduled for the current My Day.
     */
    public function isMyDay(): bool
    {
        return $this->my_day_on !== null && $this->my_day_on->isToday();
    }

    /**
     * Get the due date of the next occurrence of a repeating task.
     */
    public function nextOccurrence(): ?CarbonInterface
    {
        if ($this->repeat_type === TodoRepeat::None) {
            return $this->due_at;
        }

        return $this->repeat_type->next($this->due_at ?? Carbon::today());
    }

    /**
     * Limit the query to the tasks that are not completed.
     *
     * @param  Builder<TodoTask>  $query
     */
    public function scopeIncomplete(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    /**
     * Limit the query to the tasks that are completed.
     *
     * @param  Builder<TodoTask>  $query
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('completed_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'date',
            'remind_at' => 'datetime',
            'repeat_type' => TodoRepeat::class,
            'is_important' => 'boolean',
            'my_day_on' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
