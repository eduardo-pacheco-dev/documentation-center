<?php

namespace Plugins\Projects\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\Projects\Database\Factories\BaselineFactory;

#[Fillable(['project_id', 'created_by', 'name', 'saved_at'])]
class Baseline extends Model
{
    /** @use HasFactory<BaselineFactory> */
    use HasFactory;

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): BaselineFactory
    {
        return BaselineFactory::new();
    }

    /**
     * The project the baseline belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user that saved the baseline.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The frozen copy of every task of the project.
     *
     * @return HasMany<BaselineTask, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(BaselineTask::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
        ];
    }
}
