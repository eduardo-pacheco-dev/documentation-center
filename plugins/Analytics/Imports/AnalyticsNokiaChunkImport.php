<?php

namespace Plugins\Analytics\Imports;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Streams the Nokia spreadsheet in chunks so large files stay within memory.
 */
class AnalyticsNokiaChunkImport implements SkipsEmptyRows, ToCollection, WithChunkReading, WithHeadingRow
{
    /**
     * @param  Closure(array<int, array<string, mixed>>): void  $onChunk
     */
    public function __construct(
        private readonly Closure $onChunk,
        private readonly int $size = 1000,
    ) {}

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        ($this->onChunk)($rows
            ->map(fn (Collection $row): array => $row->toArray())
            ->all());
    }

    public function chunkSize(): int
    {
        return $this->size;
    }
}
