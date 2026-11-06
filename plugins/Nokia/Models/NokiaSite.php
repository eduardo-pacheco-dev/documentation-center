<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\Nokia\Database\Factories\NokiaSiteFactory;

#[Fillable([
    'site_id',
    'nokia_rom_id',
    'end_id',
    'oc',
    'os_fam_entrega',
    'os_fam_instalacao',
    'checklist_tarefas_enviadas',
    'checklist_fam_entrega',
    'checklist_fam_instalacao',
    'checklist_fam_panoramica',
])]
class NokiaSite extends Model
{
    /** @use HasFactory<NokiaSiteFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_sites';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): NokiaSiteFactory
    {
        return NokiaSiteFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checklist_tarefas_enviadas' => 'boolean',
            'checklist_fam_entrega' => 'boolean',
            'checklist_fam_instalacao' => 'boolean',
            'checklist_fam_panoramica' => 'boolean',
        ];
    }

    /**
     * Delete the stored attachments when the site is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (NokiaSite $site): void {
            $site->attachments()->get()->each->delete();
        });
    }

    /**
     * The files attached to the site.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(NokiaSiteAttachment::class, 'nokia_site_id')->latest('id');
    }

    /**
     * The configurable tasks tracked for the site.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(NokiaTask::class, 'nokia_site_task', 'nokia_site_id', 'nokia_task_id')
            ->withPivot('completed_at')
            ->withTimestamps()
            ->orderBy('position')
            ->orderBy('name');
    }

    /**
     * The ROM responsible for the site.
     */
    public function rom(): BelongsTo
    {
        return $this->belongsTo(NokiaRom::class, 'nokia_rom_id');
    }

    /**
     * Filter the sites by a search term.
     */
    public function scopeMatching(Builder $query, string $term): void
    {
        $like = "%{$term}%";

        $query->where(function (Builder $query) use ($like): void {
            $query->where('site_id', 'like', $like)
                ->orWhere('end_id', 'like', $like)
                ->orWhere('oc', 'like', $like)
                ->orWhere('os_fam_entrega', 'like', $like)
                ->orWhere('os_fam_instalacao', 'like', $like);
        });
    }

    /**
     * Only include sites with every checklist item completed.
     */
    public function scopeCompleted(Builder $query): void
    {
        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            $query->where($field, true);
        }
    }

    /**
     * Whether every checklist item is completed.
     */
    public function isCompleted(): bool
    {
        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            if (! $this->{$field}) {
                return false;
            }
        }

        return true;
    }
}
