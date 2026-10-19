<?php

namespace App\Models;

use Database\Factories\WorkOrderClientOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'work_order_id',
    'number',
    'description',
])]
class WorkOrderClientOrder extends Model
{
    /** @use HasFactory<WorkOrderClientOrderFactory> */
    use HasFactory;

    /**
     * The work order the client order belongs to.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * The line items assigned to the client order.
     *
     * @return HasMany<WorkOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }
}
