<?php

namespace Plugins\FollowUp\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\FollowUp\Database\Factories\FollowUpUpdateFactory;
use Plugins\FollowUp\Enums\FollowUpStatus;

#[Fillable([
    'follow_up_id',
    'user_id',
    'body',
    'status_from',
    'status_to',
])]
class FollowUpUpdate extends Model
{
    /** @use HasFactory<FollowUpUpdateFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_updates';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): FollowUpUpdateFactory
    {
        return FollowUpUpdateFactory::new();
    }

    /**
     * The follow-up the entry belongs to.
     */
    public function followUp(): BelongsTo
    {
        return $this->belongsTo(FollowUp::class);
    }

    /**
     * The user that wrote the entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_from' => FollowUpStatus::class,
            'status_to' => FollowUpStatus::class,
        ];
    }
}
