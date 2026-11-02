<?php

namespace Plugins\Stations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plugins\Stations\Enums\StationLinkStatus;

#[Fillable([
    'user_id',
    'station_a_id',
    'station_b_id',
    'code',
    'equipment_a',
    'equipment_b',
    'frequency',
    'bandwidth',
    'capacity',
    'polarization',
    'status',
    'notes',
])]
class StationLink extends Model
{
    use SoftDeletes;

    /**
     * The user that created the link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The A end of the link.
     */
    public function stationA(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_a_id');
    }

    /**
     * The B end of the link.
     */
    public function stationB(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_b_id');
    }

    /**
     * Restrict a query to the links the user owns.
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
            'status' => StationLinkStatus::class,
            'frequency' => 'decimal:3',
            'bandwidth' => 'decimal:2',
            'capacity' => 'decimal:2',
        ];
    }
}
