<?php

namespace App\Models;

use App\Enums\DevDocCategory;
use Database\Factories\DevDocFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'slug',
    'category',
    'summary',
    'content',
    'is_published',
])]
class DevDoc extends Model
{
    /** @use HasFactory<DevDocFactory> */
    use HasFactory;

    /**
     * The user that wrote the document.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => DevDocCategory::class,
            'is_published' => 'boolean',
        ];
    }
}
