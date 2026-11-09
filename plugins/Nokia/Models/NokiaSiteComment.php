<?php

namespace Plugins\Nokia\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Nokia\Database\Factories\NokiaSiteCommentFactory;

#[Fillable([
    'nokia_site_id',
    'user_id',
    'body',
])]
class NokiaSiteComment extends Model
{
    /** @use HasFactory<NokiaSiteCommentFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_site_comments';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): NokiaSiteCommentFactory
    {
        return NokiaSiteCommentFactory::new();
    }

    /**
     * The site the comment belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(NokiaSite::class, 'nokia_site_id');
    }

    /**
     * The user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
