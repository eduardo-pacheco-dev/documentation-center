<?php

declare(strict_types=1);

namespace Plugins\Analytics\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class AnalyticsImport implements SkipsEmptyRows, ToCollection, WithCustomCsvSettings, WithHeadingRow
{
    /**
     * The rows read from the spreadsheet, keyed by their slugged headings.
     *
     * @var list<array<string, mixed>>
     */
    public array $rows = [];

    public function __construct(private readonly string $delimiter = ',') {}

    /**
     * Collect the spreadsheet rows for the importer service.
     *
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        $this->rows = $rows
            ->map(fn (Collection $row): array => $row->all())
            ->values()
            ->all();
    }

    /**
     * CSV settings used while reading the uploaded file.
     *
     * @return array<string, string>
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->delimiter,
        ];
    }
}
