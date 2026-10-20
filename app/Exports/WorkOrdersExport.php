<?php

namespace App\Exports;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkOrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly string $search,
        private readonly ?string $status,
        private readonly string $sort,
        private readonly string $direction,
    ) {}

    /**
     * The work orders the authenticated user owns, after the given filters.
     */
    public function query(): Builder
    {
        return WorkOrder::query()
            ->ownedBy($this->user)
            ->with(['client:id,name', 'erb:id,site_id,code', 'radioLink:id,code'])
            ->withCount('items')
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('number', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn (Builder $client) => $client->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status !== null, fn (Builder $query) => $query->where('status', $this->status))
            ->orderBy($this->sort, $this->direction)
            ->orderBy('id', $this->direction);
    }

    /**
     * The heading row of the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Número',
            'OS Cliente Principal',
            'OC',
            'Cliente',
            'ERB',
            'Radio link',
            'Descrição',
            'Prioridade',
            'Status',
            'Abertura',
            'Previsão',
            'Concluída em',
            'Itens',
            'Total',
            'Observações',
        ];
    }

    /**
     * The values written for each work order row.
     *
     * @return array<int, string|int|float>
     */
    public function map(mixed $workOrder): array
    {
        return [
            $workOrder->number,
            $workOrder->client_os ?? '',
            $workOrder->oc ?? '',
            $workOrder->client?->name ?? '',
            $workOrder->erb ? ($workOrder->erb->site_id ?: $workOrder->erb->code) : '',
            $workOrder->radioLink?->code ?? '',
            $workOrder->description ?? '',
            $workOrder->priority?->label() ?? '',
            $workOrder->status?->label() ?? '',
            $workOrder->opened_at?->format('d/m/Y') ?? '',
            $workOrder->due_at?->format('d/m/Y') ?? '',
            $workOrder->completed_at?->format('d/m/Y') ?? '',
            $workOrder->items_count,
            round((float) $workOrder->total, 2),
            $workOrder->notes ?? '',
        ];
    }
}
