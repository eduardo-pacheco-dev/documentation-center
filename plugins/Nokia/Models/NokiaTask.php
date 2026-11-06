<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'position',
])]
class NokiaTask extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_tasks';

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    /**
     * The sites associated with the task.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(NokiaSite::class, 'nokia_site_task', 'nokia_task_id', 'nokia_site_id')
            ->withPivot('completed_at')
            ->withTimestamps();
    }
}
