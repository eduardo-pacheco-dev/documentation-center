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
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Assign a sequential code when one is not provided.
     */
    protected static function booted(): void
    {
        static::saving(function (CatalogItem $item): void {
            if (blank($item->code) && $item->user_id !== null) {
                $item->code = static::generateCode($item->user);
            }
        });
    }

    /**
     * Generate the next sequential code for the user's catalog items.
     */
    public static function generateCode(User $user): string
    {
        $sequence = $user->catalogItems()->withTrashed()->count() + 1;

        do {
            $code = 'COD-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while ($user->catalogItems()->withTrashed()->where('code', $code)->exists());

        return $code;
    }

    /**
     * The user that created the catalog item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The work order lines that reference the catalog item.
     *
     * @return HasMany<WorkOrderItem, $this>
     */
    public function workOrderItems(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
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
