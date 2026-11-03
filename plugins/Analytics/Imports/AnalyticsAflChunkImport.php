<?php

namespace Plugins\Analytics\Imports;

use Closure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

/**
 * Streams the AFL spreadsheet in chunks so large files stay within memory.
 */
class AnalyticsAflChunkImport implements SkipsEmptyRows, ToCollection, WithChunkReading
{
    /**
     * @param  Closure(array<int, array<int, mixed>>): void  $onChunk
     */
    public function __construct(
        private readonly Closure $onChunk,
        private readonly int $size = 1000,
    ) {}

    /**
     * @param  Collection<int, Collection<int, mixed>>  $rows
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
