<?php

namespace App\Exports;

use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ColaboradoresExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly string $search,
        private readonly ?string $status,
    ) {}

    /**
     * The colaboradores the authenticated user owns, after the given filters.
     */
    public function query(): Builder
    {
        return Colaborador::query()
            ->ownedBy($this->user)
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('role', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('document', 'like', "%{$this->search}%")))
            ->when($this->status !== null, fn (Builder $query) => $query->where('status', $this->status))
            ->orderBy('name')
            ->orderBy('id');
    }

    /**
     * The heading row of the spreadsheet.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Nome', 'Cargo', 'CPF', 'Telefone', 'E-mail', 'Status', 'Observações'];
    }

    /**
     * The values written for each colaborador row.
     *
     * @return array<int, string>
     */
    public function map(mixed $colaborador): array
    {
        return [
            $colaborador->name,
            $colaborador->role ?? '',
            $colaborador->document ?? '',
            $colaborador->phone ?? '',
            $colaborador->email ?? '',
            $colaborador->status?->label() ?? '',
            $colaborador->notes ?? '',
        ];
    }
}
