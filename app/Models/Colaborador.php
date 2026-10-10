<?php

namespace App\Models;

use App\Enums\ColaboradorStatus;
use Database\Factories\ColaboradorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'name',
    'role',
    'document',
    'phone',
    'email',
    'status',
    'notes',
])]
class Colaborador extends Model
{
    /** @use HasFactory<ColaboradorFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'colaboradores';

    /**
     * The user that registered the colaborador.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restrict a query to the colaboradores the user owns.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ColaboradorStatus::class,
        ];
    }
}
