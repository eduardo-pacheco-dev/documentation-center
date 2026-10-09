<?php

namespace App\Models;

use App\Enums\ProjectRole;
use App\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'work_order_id',
    'name',
    'description',
    'status',
    'start_date',
    'finish_date',
    'status_date',
    'priority',
    'currency',
    'budget',
    'percent_complete',
    'scheduled_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The user that created the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The work order this project was created from.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * The members that have access to the project.
     *
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * The users that have access to the project.
     *
     * @return BelongsToMany<User, $this>
     */
    public function contributors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role')->withTimestamps();
    }

    /**
     * The tasks that make up the project plan.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * The calendars available to the project.
     *
     * @return HasMany<Calendar, $this>
     */
    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }

    /**
     * The resources assigned to the project.
     *
     * @return HasMany<resource, $this>
     */
    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * The saved baselines of the project.
     *
     * @return HasMany<Baseline, $this>
     */
    public function baselines(): HasMany
    {
        return $this->hasMany(Baseline::class);
    }

    /**
     * The dependencies between the tasks of the project.
     *
     * @return HasMany<TaskDependency, $this>
     */
    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class);
    }

    /**
     * The resource allocations made in the project tasks.
     *
     * @return HasManyThrough<ResourceAssignment, Task, $this>
     */
    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(ResourceAssignment::class, Task::class, 'project_id', 'task_id');
    }

    /**
     * Restrict a query to the projects the user can open.
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $inner) use ($user) {
            $inner->where('user_id', $user->getKey())
                ->orWhereHas('members', fn (Builder $members) => $members->where('user_id', $user->getKey()));
        });
    }

    /**
     * The role the given user has in this project.
     */
    public function roleFor(User $user): ?ProjectRole
    {
        if ($this->user_id === $user->getKey()) {
            return ProjectRole::Owner;
        }

        $role = $this->members->firstWhere('user_id', $user->getKey())?->role;

        if ($role === null) {
            return null;
        }

        return $role instanceof ProjectRole ? $role : ProjectRole::from($role);
    }

    /**
     * Whether the given user can open the project.
     */
    public function hasMember(User $user): bool
    {
        return $this->roleFor($user) !== null;
    }

    /**
     * The calendar used to schedule the project work.
     */
    public function defaultCalendar(): Calendar
    {
        return $this->calendars()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'date',
            'finish_date' => 'date',
            'status_date' => 'date',
            'priority' => 'integer',
            'budget' => 'decimal:2',
            'percent_complete' => 'decimal:2',
            'scheduled_at' => 'datetime',
        ];
    }
}
