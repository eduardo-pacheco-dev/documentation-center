<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly User $user,
        private readonly string $search,
    ) {}

    /**
     * The clients the authenticated user owns, after the given filters.
     */
    public function query(): Builder
    {
        return Client::query()
            ->ownedBy($this->user)
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('document', 'like', "%{$this->search}%")
                ->orWhere('city', 'like', "%{$this->search}%")))
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
        return [
            'Nome',
            'CPF/CNPJ',
            'E-mail',
            'Telefone',
            'Site',
            'Rua',
            'Número',
            'Complemento',
            'Bairro',
            'Cidade',
            'UF',
            'CEP',
            'Status',
            'Observações',
        ];
    }

    /**
     * The values written for each client row.
     *
     * @return array<int, string>
     */
    public function map(mixed $client): array
    {
        return [
            $client->name,
            $client->document ?? '',
            $client->email ?? '',
            $client->phone ?? '',
            $client->website ?? '',
            $client->street ?? '',
            $client->number ?? '',
            $client->complement ?? '',
            $client->neighborhood ?? '',
            $client->city ?? '',
            $client->state ?? '',
            $client->zip ?? '',
            $client->status?->label() ?? '',
            $client->notes ?? '',
        ];
    }
}
