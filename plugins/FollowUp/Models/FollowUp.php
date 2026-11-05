<?php

namespace Plugins\FollowUp\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\FollowUp\Database\Factories\FollowUpFactory;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;

#[Fillable([
    'title',
    'code',
    'description',
    'client_id',
    'project_id',
    'status',
    'priority',
    'due_date',
    'assignee_id',
    'created_by',
    'completed_at',
])]
class FollowUp extends Model
{
    /** @use HasFactory<FollowUpFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'follow_ups';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): FollowUpFactory
    {
        return FollowUpFactory::new();
    }

    /**
     * The client linked to the follow-up.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(FollowUpClient::class, 'client_id');
    }

    /**
     * The project linked to the follow-up.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(FollowUpProject::class, 'project_id');
    }

    /**
     * The person responsible for the follow-up.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(FollowUpAssignee::class, 'assignee_id');
    }

    /**
     * The user that created the follow-up.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The timeline entries of the follow-up.
     */
    public function updates(): HasMany
    {
        return $this->hasMany(FollowUpUpdate::class)->latest();
    }

    /**
     * Filter by title, code or description.
     */
    public function scopeMatching(Builder $query, string $term): void
    {
        $like = "%{$term}%";

        $query->where(function (Builder $query) use ($like): void {
            $query->where('title', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('client', fn (Builder $query) => $query->where('name', 'like', $like))
                ->orWhereHas('project', fn (Builder $query) => $query->where('name', 'like', $like));
        });
    }

    /**
     * Only follow-ups that are still open.
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', [
            FollowUpStatus::Completed->value,
            FollowUpStatus::Cancelled->value,
        ]);
    }

    /**
     * Only follow-ups past their due date.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->open()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    /**
     * Whether the follow-up is past its due date.
     */
    public function isOverdue(): bool
    {
        return ! $this->status->isClosed()
            && $this->due_date !== null
            && $this->due_date->lt(today());
    }

    /**
     * Whether the follow-up is due within the next seven days.
     */
    public function isDueSoon(): bool
    {
        return ! $this->status->isClosed()
            && $this->due_date !== null
            && $this->due_date->betweenIncluded(
                today(),
                today()->addDays((int) config('follow-up.due_soon_days', 7)),
            );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FollowUpStatus::class,
            'priority' => FollowUpPriority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }
}
