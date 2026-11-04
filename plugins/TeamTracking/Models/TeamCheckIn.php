<?php

namespace Plugins\TeamTracking\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\TeamTracking\Database\Factories\TeamCheckInFactory;
use Plugins\TeamTracking\Enums\CheckInStatus;

#[Fillable([
    'team_id',
    'user_id',
    'title',
    'reference_date',
    'status',
    'health_score',
    'summary',
    'next_steps',
])]
class TeamCheckIn extends Model
{
    /** @use HasFactory<TeamCheckInFactory> */
    use HasFactory;

    protected $table = 'team_check_ins';

    protected static function newFactory(): TeamCheckInFactory
    {
        return TeamCheckInFactory::new();
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'reference_date' => 'date',
            'status' => CheckInStatus::class,
            'health_score' => 'integer',
        ];
    }
}
