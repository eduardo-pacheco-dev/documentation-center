<?php

namespace App\Exports;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WorkOrdersCompleteExport implements FromCollection, WithHeadings
{
    /**
     * The simplified export used as the base of the complete one.
     */
    private readonly WorkOrdersExport $base;

    public function __construct(
        User $user,
        string $search,
        ?string $status,
        string $sort,
        string $direction,
    ) {
        $this->base = new WorkOrdersExport($user, $search, $status, $sort, $direction);
    }

    /**
     * One row per work order item, repeating the work order data.
     *
     * @return Collection<int, array<int, mixed>>
     */
    public function collection(): Collection
    {
        return $this->base->query()
            ->with(['clientOrders', 'items'])
            ->get()
            ->flatMap(function (WorkOrder $workOrder): array {
                $base = $this->base->map($workOrder);
                $base[] = $workOrder->clientOrders
                    ->map(fn ($clientOrder): string => trim(
                        $clientOrder->number.($clientOrder->description ? ' - '.$clientOrder->description : ''),
                    ))
                    ->implode(' | ');

                if ($workOrder->items->isEmpty()) {
                    return [array_merge($base, ['', '', '', '', '', '', '', ''])];
                }

                return $workOrder->items
                    ->map(fn ($item): array => array_merge($base, [
                        $item->description,
                        $item->client_os ?? '',
                        $item->client_os_2 ?? '',
                        $item->unit ?? '',
                        (float) $item->quantity,
                        (float) $item->unit_price,
                        (float) $item->total,
                        $item->status?->label() ?? '',
                    ]))
                    ->all();
            })
            ->values();
    }

    /**
     * The heading row of the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            ...$this->base->headings(),
            'OS do cliente (lista)',
            'Item',
            'OS cliente 1 do item',
            'OS cliente 2 do item',
            'Unidade',
            'Quantidade',
            'Valor unitário',
            'Subtotal do item',
            'Status do item',
        ];
    }
}
