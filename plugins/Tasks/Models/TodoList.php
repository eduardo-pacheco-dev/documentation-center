<?php

namespace Plugins\Tasks\Models;

use Plugins\Tasks\Enums\TodoColor;
use Plugins\Tasks\Enums\TodoIcon;
use Plugins\Tasks\Database\Factories\TodoListFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

#[Fillable(['user_id', 'name', 'color', 'icon', 'position'])]
class TodoList extends Model
{
    /** @use HasFactory<TodoListFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): TodoListFactory
    {
        return TodoListFactory::new();
    }

    /**
     * The user that owns the list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The tasks that belong to the list.
     *
     * @return HasMany<TodoTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(TodoTask::class);
    }

    /**
     * Order the lists by their position.
     *
     * @param  Builder<TodoList>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'color' => TodoColor::class,
            'icon' => TodoIcon::class,
        ];
    }
}
