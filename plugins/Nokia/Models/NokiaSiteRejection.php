<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'nokia_site_id',
    'checklist_field',
    'reason',
    'return_days',
    'requires_field_return',
    'started_at',
    'resolved_at',
])]
class NokiaSiteRejection extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_site_rejections';

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_days' => 'integer',
            'requires_field_return' => 'boolean',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Whether the rejection is still active.
     */
    public function isActive(): bool
    {
        return $this->resolved_at === null;
    }

    /**
     * The site the rejection belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(NokiaSite::class, 'nokia_site_id');
    }

    /**
     * The expected return date for the rejection.
     */
    public function returnAt(): Carbon
    {
        return $this->started_at->copy()->addDays($this->return_days);
    }

    /**
     * The checklist item label related to the rejection.
     */
    public function checklistLabel(): string
    {
        return config('nokia.checklists', [])[$this->checklist_field] ?? $this->checklist_field;
    }
}
