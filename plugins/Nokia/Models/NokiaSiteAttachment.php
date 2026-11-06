<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Nokia\Database\Factories\NokiaSiteAttachmentFactory;

#[Fillable([
    'nokia_site_id',
    'category',
    'original_name',
    'path',
    'size',
    'mime_type',
])]
class NokiaSiteAttachment extends Model
{
    /** @use HasFactory<NokiaSiteAttachmentFactory> */
    use HasFactory;

    public const CATEGORY_TSSR = 'tssr';

    public const CATEGORY_DOC_D = 'doc-d';

    public const CATEGORY_DWG = 'dwg';

    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_site_attachments';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): NokiaSiteAttachmentFactory
    {
        return NokiaSiteAttachmentFactory::new();
    }

    /**
     * Delete the stored file when the attachment record is removed.
     */
    protected static function booted(): void
    {
        static::deleting(function (NokiaSiteAttachment $attachment): void {
            plugin_storage('nokia')->delete($attachment->path);
        });
    }

    /**
     * The available attachment categories.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_TSSR => 'TSSR',
            self::CATEGORY_DOC_D => 'DOC-D',
            self::CATEGORY_DWG => 'DWG',
        ];
    }

    /**
     * The site that owns the attachment.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(NokiaSite::class, 'nokia_site_id');
    }

    /**
     * The human readable file size.
     */
    public function humanSize(): string
    {
        $bytes = max(0, (int) $this->size);
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) min(floor(log($bytes, 1024)), count($units) - 1) : 0;

        return number_format($bytes / (1024 ** $power), $power === 0 ? 0 : 1, ',', '.').' '.$units[$power];
    }
}
