<?php

namespace Plugins\TeamTracking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Plugins\TeamTracking\Database\Factories\TeamFactory;
use Plugins\TeamTracking\Enums\TeamStatus;

#[Fillable([
    'name',
    'description',
    'area',
    'leader_id',
    'status',
    'started_at',
])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'teams';

    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(TeamCheckIn::class)->latest('reference_date')->latest('id');
    }

    public function latestCheckIn(): HasOne
    {
        return $this->hasOne(TeamCheckIn::class)->latestOfMany('reference_date');
    }

    public function scopeMatching(Builder $query, string $term): void
    {
        $like = "%{$term}%";

        $query->where(function (Builder $query) use ($like): void {
            $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('area', 'like', $like);
        });
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', TeamStatus::Active->value);
    }

    protected function casts(): array
    {
        return [
            'status' => TeamStatus::class,
            'started_at' => 'date',
        ];
    }
}
