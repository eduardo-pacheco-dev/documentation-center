<?php

namespace Plugins\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'oc',
    'os',
    'created_on',
    'supplier',
    'audit_supplier',
    'project_type',
    'process_stage',
    'element_type',
    'address',
    'city',
    'state',
    'node_name',
    'location',
    'operator',
    'status',
])]
class AnalyticsRecord extends Model
{
    /**
     * The user that imported the record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restrict a query to the records the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_on' => 'date',
        ];
    }
}
