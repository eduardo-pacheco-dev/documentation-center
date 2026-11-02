<?php

namespace Plugins\Analytics\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;

class AnalyticsAflImport implements SkipsEmptyRows, ToArray
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void
    {
        //
    }
}
