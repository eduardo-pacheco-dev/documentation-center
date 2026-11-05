<?php

namespace Plugins\Nokia\Support;

use RuntimeException;

final class NokiaSiteImportException extends RuntimeException
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(public readonly array $errors = [])
    {
        parent::__construct($errors[0] ?? 'Não foi possível importar os sites.');
    }
}
