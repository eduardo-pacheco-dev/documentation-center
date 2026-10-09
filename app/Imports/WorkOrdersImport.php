<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkOrdersImport implements SkipsEmptyRows, ToArray, WithHeadingRow
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void
    {
        //
    }
}
