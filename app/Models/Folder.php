<?php

namespace App\Models;

use Database\Factories\FolderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'parent_id', 'name'])]
class Folder extends Model
{
    /** @use HasFactory<FolderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The user that owns the folder.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The folder that contains this folder, if any.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The folders nested inside this folder.
     *
     * @return HasMany<Folder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The files stored in this folder.
     *
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Cascade deletes through the folder tree.
     *
     * A soft delete moves child folders and files to the trash; a forced
     * delete purges them, which also removes the underlying storage files.
     */
    protected static function booted(): void
    {
        static::deleting(function (Folder $folder): void {
            if ($folder->isForceDeleting()) {
                $folder->children()->withTrashed()->get()->each->forceDelete();
                $folder->documents()->withTrashed()->get()->each->forceDelete();

                return;
            }

            $folder->children()->get()->each->delete();
            $folder->documents()->get()->each->delete();
        });
    }

    /**
     * Restore this folder together with every descendant and the ancestor chain.
     */
    public function restoreTree(): void
    {
        $this->restoreAncestors();

        $this->restore();

        $this->children()->onlyTrashed()->get()->each(fn (Folder $child) => $child->restoreTree());
        $this->documents()->onlyTrashed()->get()->each->restore();
    }

    /**
     * Restore only the ancestor folders still in the trash, leaving their other
     * contents in the trash.
     */
    protected function restoreAncestors(): void
    {
        $parent = $this->parent()->withTrashed()->first();

        if ($parent === null || ! $parent->trashed()) {
            return;
        }

        $parent->restoreAncestors();
        $parent->restore();
    }

    /**
     * Get the folder and its ancestors from the root down to itself.
     *
     * @return list<Folder>
     */
    public function ancestors(): array
    {
        $trail = [$this];
        $cursor = $this->parent;

        while ($cursor !== null) {
            array_unshift($trail, $cursor);
            $cursor = $cursor->parent;
        }

        return $trail;
    }
}
