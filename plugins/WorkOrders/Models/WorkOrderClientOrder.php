<?php

namespace Plugins\WorkOrders\Models;

use Plugins\WorkOrders\Database\Factories\WorkOrderClientOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): WorkOrderClientOrderFactory
    {
        return WorkOrderClientOrderFactory::new();
    }

    /**
     * The work order the client order belongs to.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
