<?php

namespace Plugins\Stations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plugins\Stations\Database\Factories\StationFactory;
use Plugins\Stations\Enums\StationStatus;

#[Fillable([
    'user_id',
    'code',
    'assigned_to',
    'name',
    'site_id',
    'end_id',
    'operator',
    'technology',
    'nokia_smp_id',
    'start_date',
    'end_date',
    'service_order_id',
    'status',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
    'zip',
    'latitude',
    'longitude',
    'notes',
])]
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Assign a sequential code and derive the name when they are not provided.
     */
    protected static function booted(): void
    {
        static::saving(function (Station $station): void {
            if (blank($station->code) && $station->user_id !== null) {
                $station->code = static::generateCode($station->user);
            }

            if (blank($station->name)) {
                $station->name = $station->site_id ?: ($station->end_id ?: $station->code);
            }
        });
    }

    /**
     * Generate the next sequential code for the user's stations.
     */
    public static function generateCode(User $user): string
    {
        $prefix = (string) config('stations.code_prefix', 'ST');
        $query = static::query()->where('user_id', $user->getKey())->withTrashed();
        $sequence = (clone $query)->count() + 1;

        do {
            $code = $prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ((clone $query)->where('code', $code)->exists());

        return $code;
    }

    /**
     * The user that created the station.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The files attached to the station.
     *
     * @return HasMany<StationDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(StationDocument::class)->latest();
    }

    /**
     * The comments made about the station.
     *
     * @return HasMany<StationComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(StationComment::class)->oldest();
    }

    /**
     * The links where the station is the A end.
     *
     * @return HasMany<StationLink, $this>
     */
    public function linksAsEndA(): HasMany
    {
        return $this->hasMany(StationLink::class, 'station_a_id');
    }

    /**
     * The links where the station is the B end.
     *
     * @return HasMany<StationLink, $this>
     */
    public function linksAsEndB(): HasMany
    {
        return $this->hasMany(StationLink::class, 'station_b_id');
    }

    /**
     * Restrict a query to the stations the user owns.
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
            'status' => StationStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): StationFactory
    {
        return StationFactory::new();
    }
}
