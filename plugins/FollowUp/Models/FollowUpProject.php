<?php

namespace Plugins\FollowUp\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\FollowUp\Database\Factories\FollowUpProjectFactory;

#[Fillable(['client_id', 'name'])]
class FollowUpProject extends Model
{
    /** @use HasFactory<FollowUpProjectFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_projects';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): FollowUpProjectFactory
    {
        return FollowUpProjectFactory::new();
    }

    /**
     * The client that owns the project.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(FollowUpClient::class, 'client_id');
    }

    /**
     * The follow-ups linked to the project.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'project_id');
    }
}
