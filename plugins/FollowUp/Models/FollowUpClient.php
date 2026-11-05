<?php

namespace Plugins\FollowUp\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\FollowUp\Database\Factories\FollowUpClientFactory;

#[Fillable(['name'])]
class FollowUpClient extends Model
{
    /** @use HasFactory<FollowUpClientFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_clients';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): FollowUpClientFactory
    {
        return FollowUpClientFactory::new();
    }

    /**
     * The projects registered for the client.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(FollowUpProject::class, 'client_id')->orderBy('name');
    }

    /**
     * The follow-ups linked to the client.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'client_id');
    }
}
