<?php

namespace Plugins\Nokia\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Keeps the XLSX reader from loading the entire spreadsheet into memory.
 *
 * The importer only accepts a small number of rows, so anything beyond the
 * configured limits is discarded before PhpSpreadsheet creates the cells.
 */
final class NokiaImportReadFilter implements IReadFilter
{
    public function __construct(
        private readonly int $maxRows,
        private readonly int $maxColumns,
    ) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        if ($row > $this->maxRows) {
            return false;
        }

        return Coordinate::columnIndexFromString($columnAddress) <= $this->maxColumns;
    }
}
