<?php

namespace Plugins\Analytics\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'oc',
    'site_id',
    'city',
    'uf',
    'regional',
    'project',
    'gap_status',
    'macro_status',
    'status_geral',
    'data',
])]
class AnalyticsNokiaRecord extends AnalyticsModel
{
    /**
     * The user that imported the record.
     */
    public function user(): BelongsTo
    {
        $related = (new User)->setConnection(config('database.default'));

        return $this->newBelongsTo(
            $related->newQuery(),
            $this,
            'user_id',
            $related->getKeyName(),
            'user',
        );
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
            'data' => 'array',
        ];
    }
}
