<?php

namespace App\Models;

use App\Enums\DependencyType;
use Database\Factories\TaskDependencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'predecessor_id', 'successor_id', 'type', 'lag_minutes'])]
class TaskDependency extends Model
{
    /** @use HasFactory<TaskDependencyFactory> */
    use HasFactory;

    /**
     * The project the dependency belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The task that must move first.
     */
    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'predecessor_id');
    }

    /**
     * The task that is scheduled from the predecessor.
     */
    public function successor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'successor_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DependencyType::class,
            'lag_minutes' => 'integer',
        ];
    }
}
