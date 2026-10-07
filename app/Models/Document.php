<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'uploaded_via_short_link_id', 'original_name', 'path', 'disk', 'mime_type', 'size'])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    /**
     * The user that owns the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The upload link that received this file, if any.
     */
    public function uploadedVia(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class, 'uploaded_via_short_link_id');
    }

    /**
     * The download links sharing this file.
     */
    public function shortLinks(): BelongsToMany
    {
        return $this->belongsToMany(ShortLink::class, 'link_document')->withTimestamps();
    }

    /**
     * Remove the underlying file when the document is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Document $document): void {
            Storage::disk($document->disk)->delete($document->path);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }
}
