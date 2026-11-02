<?php

namespace Plugins\Clients\Models;

use Plugins\Clients\Database\Factories\ClientContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'name',
    'position',
    'phone',
    'email',
    'is_primary',
])]
class ClientContact extends Model
{
    /** @use HasFactory<ClientContactFactory> */
    use HasFactory;

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): ClientContactFactory
    {
        return ClientContactFactory::new();
    }

    /**
     * The client the contact belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
