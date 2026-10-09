<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['user_id', 'uploaded_via_short_link_id', 'folder_id', 'client_id', 'erb_id', 'original_name', 'path', 'disk', 'mime_type', 'size'])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory, SoftDeletes;

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
     * The folder holding this file, if any.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * The client this file is attached to, if any.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The ERB this file is attached to, if any.
     */
    public function erb(): BelongsTo
    {
        return $this->belongsTo(Erb::class);
    }

    /**
     * The download links sharing this file.
     */
    public function shortLinks(): BelongsToMany
    {
        return $this->belongsToMany(ShortLink::class, 'link_document')->withTimestamps();
    }

    /**
     * Remove the underlying file only when the document is permanently deleted.
     * Files sent to the trash keep their storage copy until the trash is emptied.
     */
    protected static function booted(): void
    {
        static::deleting(function (Document $document): void {
            if ($document->isForceDeleting()) {
                Storage::disk($document->disk)->delete($document->path);
            }
        });
    }

    /**
     * Restore the document and any ancestor folder still in the trash.
     */
    public function restoreWithAncestors(): void
    {
        $this->restore();

        $folder = $this->folder()->withTrashed()->first();

        while ($folder !== null && $folder->trashed()) {
            $folder->restore();
            $folder = $folder->parent()->withTrashed()->first();
        }
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
