<?php

namespace Plugins\FollowUp\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\FollowUp\Database\Factories\FollowUpAssigneeFactory;

#[Fillable(['name', 'email'])]
class FollowUpAssignee extends Model
{
    /** @use HasFactory<FollowUpAssigneeFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_assignees';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): FollowUpAssigneeFactory
    {
        return FollowUpAssigneeFactory::new();
    }

    /**
     * The follow-ups assigned to this person.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'assignee_id');
    }

    /**
     * The display name used across the interface and e-mails.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->name,
        );
    }
}
