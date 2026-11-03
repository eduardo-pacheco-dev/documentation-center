<?php

namespace Plugins\Stations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Plugins\Stations\Database\Factories\StationAttachmentFactory;

#[Fillable([
    'station_id',
    'uploaded_by',
    'category',
    'original_name',
    'path',
    'mime_type',
    'size',
])]
class StationAttachment extends Model
{
    /** @use HasFactory<StationAttachmentFactory> */
    use HasFactory;

    public const CATEGORY_PPI_TSSR = 'ppi_tssr';

    public const CATEGORY_ATTACHMENT = 'attachment';

    /**
     * The table associated with the model.
     */
    protected $table = 'station_attachments';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): StationAttachmentFactory
    {
        return StationAttachmentFactory::new();
    }

    /**
     * Remove the stored file when the attachment record is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (StationAttachment $attachment): void {
            Storage::disk('local')->delete($attachment->path);
        });
    }

    /**
     * The station the attachment belongs to.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * The user that uploaded the attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * The uppercase extension of the original file name.
     */
    public function extension(): string
    {
        $extension = pathinfo($this->original_name, PATHINFO_EXTENSION);

        return $extension !== '' ? mb_strtoupper($extension) : 'ARQ';
    }

    /**
     * The file size in a human readable format.
     */
    public function humanSize(): string
    {
        $size = max((int) $this->size, 0);
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return number_format($size, $index === 0 ? 0 : 1, ',', '.').' '.$units[$index];
    }
}
