<?php

namespace App\Models;

use App\Enums\RadioLinkPolarization;
use App\Enums\RadioLinkStatus;
use Database\Factories\RadioLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'erb_a_id',
    'erb_b_id',
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
class RadioLink extends Model
{
    /** @use HasFactory<RadioLinkFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The Earth radius in kilometers.
     */
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * The user that created the radio link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The ERB at the A end of the link.
     */
    public function erbA(): BelongsTo
    {
        return $this->belongsTo(Erb::class, 'erb_a_id');
    }

    /**
     * The ERB at the B end of the link.
     */
    public function erbB(): BelongsTo
    {
        return $this->belongsTo(Erb::class, 'erb_b_id');
    }

    /**
     * Restrict a query to the radio links the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * The great-circle distance between both ends, in kilometers.
     */
    public function distanceKm(): ?float
    {
        $a = $this->erbA;
        $b = $this->erbB;

        if ($a === null || $b === null) {
            return null;
        }

        if ($a->latitude === null || $a->longitude === null || $b->latitude === null || $b->longitude === null) {
            return null;
        }

        $latA = deg2rad((float) $a->latitude);
        $lonA = deg2rad((float) $a->longitude);
        $latB = deg2rad((float) $b->latitude);
        $lonB = deg2rad((float) $b->longitude);

        $deltaLat = $latB - $latA;
        $deltaLon = $lonB - $lonA;

        $haversine = sin($deltaLat / 2) ** 2 + cos($latA) * cos($latB) * sin($deltaLon / 2) ** 2;

        return round(self::EARTH_RADIUS_KM * 2 * asin(min(1.0, sqrt($haversine))), 2);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RadioLinkStatus::class,
            'polarization' => RadioLinkPolarization::class,
            'frequency' => 'decimal:3',
            'bandwidth' => 'decimal:2',
            'capacity' => 'decimal:2',
        ];
    }
}
