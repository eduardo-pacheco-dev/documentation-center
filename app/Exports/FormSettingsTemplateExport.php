<?php

namespace App\Exports;

use App\Services\Forms\FormModuleSettings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FormSettingsTemplateExport implements FromArray, WithHeadings
{
    /**
     * @param  array<int, string>  $types
     * @param  array<int, string>  $statuses
     */
    public function __construct(
        private readonly array $types,
        private readonly array $statuses,
        private readonly string $defaultView,
        private readonly int $perPage,
        private readonly ?string $defaultTemplateName = null,
    ) {}

    /**
     * The heading row of the template.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Configuração',
            'Valor',
        ];
    }

    /**
     * The rows shown in the template.
     *
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        $rows = [];

        foreach ($this->types as $type) {
            $rows[] = ['Tipo de formulário', $type];
        }

        foreach ($this->statuses as $status) {
            $rows[] = ['Status de formulário', $status];
        }

        $rows[] = ['Visualização padrão', FormModuleSettings::VIEWS[$this->defaultView] ?? $this->defaultView];
        $rows[] = ['Itens por página', $this->perPage];
        $rows[] = ['Template padrão', $this->defaultTemplateName ?? ''];

        return $rows;
    }
}
