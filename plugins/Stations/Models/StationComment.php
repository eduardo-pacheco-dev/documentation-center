<?php

namespace Plugins\Stations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Stations\Database\Factories\StationCommentFactory;

#[Fillable([
    'station_id',
    'user_id',
    'body',
])]
class StationComment extends Model
{
    /** @use HasFactory<StationCommentFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'station_comments';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): StationCommentFactory
    {
        return StationCommentFactory::new();
    }

    /**
     * The station the comment belongs to.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * The user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
