<?php

namespace App\Models;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemType;
use Database\Factories\CatalogItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'type',
    'name',
    'code',
    'unit',
    'price',
    'cost',
    'stock_quantity',
    'description',
    'notes',
    'status',
])]
class CatalogItem extends Model
{
    /** @use HasFactory<CatalogItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The user that created the catalog item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restrict a query to the catalog items the user owns.
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
            'type' => CatalogItemType::class,
            'status' => CatalogItemStatus::class,
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'stock_quantity' => 'decimal:2',
        ];
    }
}
