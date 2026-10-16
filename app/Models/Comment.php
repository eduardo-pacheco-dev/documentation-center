<?php

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'erb_id',
    'radio_link_id',
    'work_order_id',
    'body',
])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    /**
     * The user that wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The ERB the comment belongs to, when the comment was written on an ERB.
     */
    public function erb(): BelongsTo
    {
        return $this->belongsTo(Erb::class);
    }

    /**
     * The radio link the comment belongs to, when the comment was written on a radio link.
     */
    public function radioLink(): BelongsTo
    {
        return $this->belongsTo(RadioLink::class);
    }

    /**
     * The work order the comment belongs to, when the comment was written on a work order.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
