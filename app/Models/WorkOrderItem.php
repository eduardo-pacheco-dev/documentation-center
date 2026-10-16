<?php

namespace App\Models;

use App\Enums\WorkOrderItemStatus;
use Database\Factories\WorkOrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'work_order_id',
    'catalog_item_id',
    'description',
    'unit',
    'quantity',
    'unit_price',
    'total',
    'status',
])]
class WorkOrderItem extends Model
{
    /** @use HasFactory<WorkOrderItemFactory> */
    use HasFactory;

    /**
     * The work order the item belongs to.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * The catalog item this line was based on, when any.
     */
    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    /**
     * The files attached to this line item.
     *
     * @return HasMany<Document, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->latest();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => WorkOrderItemStatus::class,
        ];
    }
}
