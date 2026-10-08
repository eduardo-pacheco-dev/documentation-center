<?php

namespace App\Models;

use App\Enums\SchedulingMode;
use App\Enums\TaskConstraint;
use App\Enums\TaskType;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'parent_id',
    'name',
    'wbs',
    'outline_level',
    'sort_order',
    'task_type',
    'scheduling_mode',
    'is_milestone',
    'start_at',
    'finish_at',
    'duration_minutes',
    'work_minutes',
    'constraint_type',
    'constraint_date',
    'priority',
    'percent_complete',
    'actual_start_at',
    'actual_finish_at',
    'actual_duration_minutes',
    'actual_cost',
    'budget_cost',
    'notes',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * The project the task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The summary task that contains this task.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The tasks nested inside this task.
     *
     * @return HasMany<Task, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The dependencies where this task is the predecessor.
     *
     * @return HasMany<TaskDependency, $this>
     */
    public function successors(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'predecessor_id');
    }

    /**
     * The dependencies where this task is the successor.
     *
     * @return HasMany<TaskDependency, $this>
     */
    public function predecessors(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'successor_id');
    }

    /**
     * The resource assignments of the task.
     *
     * @return HasMany<ResourceAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
    }

    /**
     * The resources assigned to the task.
     *
     * @return BelongsToMany<resource, $this>
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'resource_assignments')->withPivot(['units', 'work_minutes', 'cost'])->withTimestamps();
    }

    /**
     * The root tasks of the project, in work breakdown order.
     *
     * @return HasMany<Task, $this>
     */
    public function roots(): HasMany
    {
        return $this->whereNull('parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Whether the task summarises nested tasks.
     */
    public function isSummary(): bool
    {
        return $this->relationLoaded('children')
            ? $this->children->isNotEmpty()
            : $this->children()->exists();
    }

    /**
     * Whether the task has no duration and represents a zero length event.
     */
    public function isInstantaneous(): bool
    {
        return $this->is_milestone || $this->isSummary();
    }

    /**
     * Whether the task is nested inside the given ancestor at any depth.
     */
    public function isDescendantOf(Task $ancestor): bool
    {
        $cursor = $this->parent_id;

        for ($depth = 0; $cursor !== null && $depth < 100; $depth++) {
            if ($cursor === $ancestor->getKey()) {
                return true;
            }

            $cursor = Task::query()->whereKey($cursor)->value('parent_id');
        }

        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'task_type' => TaskType::class,
            'scheduling_mode' => SchedulingMode::class,
            'constraint_type' => TaskConstraint::class,
            'is_milestone' => 'boolean',
            'outline_level' => 'integer',
            'sort_order' => 'integer',
            'start_at' => 'datetime',
            'finish_at' => 'datetime',
            'duration_minutes' => 'integer',
            'work_minutes' => 'integer',
            'constraint_date' => 'datetime',
            'priority' => 'integer',
            'percent_complete' => 'decimal:2',
            'actual_start_at' => 'datetime',
            'actual_finish_at' => 'datetime',
            'actual_duration_minutes' => 'integer',
            'actual_cost' => 'decimal:2',
            'budget_cost' => 'decimal:2',
            'critical' => 'boolean',
            'total_slack_minutes' => 'integer',
            'free_slack_minutes' => 'integer',
            'level_delay_minutes' => 'integer',
        ];
    }
}
