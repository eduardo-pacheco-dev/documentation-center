<?php

namespace Plugins\Stations\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StationsImport implements SkipsEmptyRows, ToArray, WithHeadingRow
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void
    {
        //
    }
}
