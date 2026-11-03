<?php

namespace Plugins\Stations\Support;

use RuntimeException;

final class StationImportException extends RuntimeException
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct($errors[0] ?? 'Não foi possível importar as estações.');
    }
}
