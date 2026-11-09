<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Plugins\Nokia\Database\Factories\NokiaSiteFactory;

#[Fillable([
    'site_id',
    'nokia_rom_id',
    'end_id',
    'oc',
    'os_fam_entrega',
    'os_fam_instalacao',
    'regional',
    'subcon',
    'interruption_reason',
    'interruption_return_days',
    'interruption_started_at',
    'checklist_fam_entrega',
    'checklist_fam_instalacao',
    'checklist_fam_panoramica',
    'checklist_fam_entrega_status',
    'checklist_fam_instalacao_status',
    'checklist_fam_panoramica_status',
    'mos_plan_at',
    'mos_real_at',
    'instalacao_plan_at',
    'instalacao_real_at',
    'integracao_plan_at',
    'integracao_real_at',
    'rfa_plan_at',
    'rfa_real_at',
    'fam_entrega_approved_at',
    'fam_instalacao_approved_at',
    'fam_panoramica_approved_at',
])]
class NokiaSite extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ANALYSIS = 'analysis';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SENT = 'sent';

    /**
     * The labels for every checklist status.
     *
     * @var array<string, string>
     */
    public const CHECKLIST_STATUSES = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_ANALYSIS => 'Análise TIM',
        self::STATUS_REJECTED => 'Rejeitado',
        self::STATUS_SENT => 'Aprovado',
    ];

    /**
     * The approval date of each checklist item.
     *
     * @var array<string, string>
     */
    public const APPROVAL_CHECKLISTS = [
        'fam_entrega_approved_at' => 'checklist_fam_entrega',
        'fam_instalacao_approved_at' => 'checklist_fam_instalacao',
        'fam_panoramica_approved_at' => 'checklist_fam_panoramica',
    ];

    /**
     * The date fields tracked for a site.
     *
     * @var list<string>
     */
    public const DATE_FIELDS = [
        'mos_plan_at',
        'mos_real_at',
        'instalacao_plan_at',
        'instalacao_real_at',
        'integracao_plan_at',
        'integracao_real_at',
        'rfa_plan_at',
        'rfa_real_at',
        'fam_entrega_approved_at',
        'fam_instalacao_approved_at',
        'fam_panoramica_approved_at',
    ];

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
            'checklist_fam_entrega' => 'boolean',
            'checklist_fam_instalacao' => 'boolean',
            'checklist_fam_panoramica' => 'boolean',
            'interruption_return_days' => 'integer',
            'interruption_started_at' => 'datetime',
            'mos_plan_at' => 'date',
            'mos_real_at' => 'date',
            'instalacao_plan_at' => 'date',
            'instalacao_real_at' => 'date',
            'integracao_plan_at' => 'date',
            'integracao_real_at' => 'date',
            'rfa_plan_at' => 'date',
            'rfa_real_at' => 'date',
            'fam_entrega_approved_at' => 'date',
            'fam_instalacao_approved_at' => 'date',
            'fam_panoramica_approved_at' => 'date',
        ];
    }

    /**
     * Whether the site has any date filled in.
     */
    public function hasDates(): bool
    {
        foreach (self::DATE_FIELDS as $field) {
            if ($this->{$field} !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * The status of the given checklist item.
     *
     * New or legacy records fall back to the boolean value so previously
     * completed items keep showing as approved.
     */
    public function checklistStatus(string $field): string
    {
        $status = $this->getAttribute($field.'_status');

        if (! is_string($status) || ! array_key_exists($status, self::CHECKLIST_STATUSES)) {
            $status = self::STATUS_PENDING;
        }

        if ($status === self::STATUS_PENDING && (bool) $this->{$field}) {
            return self::STATUS_SENT;
        }

        return $status;
    }

    /**
     * The label of the given checklist item status.
     */
    public function checklistStatusLabel(string $field): string
    {
        return self::CHECKLIST_STATUSES[$this->checklistStatus($field)];
    }

    /**
     * The badge tone of the given checklist item status.
     */
    public function checklistStatusTone(string $field): string
    {
        return match ($this->checklistStatus($field)) {
            self::STATUS_SENT => 'green',
            self::STATUS_ANALYSIS => 'amber',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * The rejections registered for the site.
     */
    public function rejections(): HasMany
    {
        return $this->hasMany(NokiaSiteRejection::class, 'nokia_site_id')->latest('started_at')->latest('id');
    }

    /**
     * The comments written about the site.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NokiaSiteComment::class, 'nokia_site_id')->latest('created_at')->latest('id');
    }

    /**
     * Whether the site has an active work interruption.
     */
    public function isInterrupted(): bool
    {
        return $this->interruption_reason !== null
            && $this->interruption_return_days !== null
            && $this->interruption_started_at !== null;
    }

    /**
     * The expected return date for the current interruption.
     */
    public function interruptionReturnAt(): ?Carbon
    {
        if (! $this->isInterrupted()) {
            return null;
        }

        return $this->interruption_started_at->copy()->addDays($this->interruption_return_days);
    }

    /**
     * Delete the stored attachments when the site is deleted.
     */
    protected static function booted(): void
    {
        static::saving(function (NokiaSite $site): void {
            foreach (self::APPROVAL_CHECKLISTS as $dateField => $checklistField) {
                if (! $site->isDirty($dateField) || $site->{$dateField} === null) {
                    continue;
                }

                $site->{$checklistField} = true;
                $site->{$checklistField.'_status'} = self::STATUS_SENT;
            }
        });

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
