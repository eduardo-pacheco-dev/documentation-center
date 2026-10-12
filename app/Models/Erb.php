<?php

namespace App\Models;

use App\Enums\ErbStatus;
use Database\Factories\ErbFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
class Erb extends Model
{
    /** @use HasFactory<ErbFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Assign a sequential code and derive the name when they are not provided.
     */
    protected static function booted(): void
    {
        static::saving(function (Erb $erb): void {
            if (blank($erb->code) && $erb->user_id !== null) {
                $erb->code = static::generateCode($erb->user);
            }

            if (blank($erb->name)) {
                $erb->name = $erb->site_id ?: ($erb->end_id ?: $erb->code);
            }
        });
    }

    /**
     * Generate the next sequential code for the user's ERBs.
     */
    public static function generateCode(User $user): string
    {
        $sequence = $user->erbs()->withTrashed()->count() + 1;

        do {
            $code = 'ERB-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($user->erbs()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * The user that created the ERB.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The work orders carried out on the ERB.
     *
     * @return HasMany<WorkOrder, $this>
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * The projects related to the ERB.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * The files attached to the ERB.
     *
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    /**
     * The comments made about the ERB.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->oldest();
    }

    /**
     * The radio links where the ERB is the A end.
     *
     * @return HasMany<RadioLink, $this>
     */
    public function radioLinksAsEndA(): HasMany
    {
        return $this->hasMany(RadioLink::class, 'erb_a_id');
    }

    /**
     * The radio links where the ERB is the B end.
     *
     * @return HasMany<RadioLink, $this>
     */
    public function radioLinksAsEndB(): HasMany
    {
        return $this->hasMany(RadioLink::class, 'erb_b_id');
    }

    /**
     * Restrict a query to the ERBs the user owns.
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
            'status' => ErbStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
