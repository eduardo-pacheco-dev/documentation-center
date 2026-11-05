<?php

declare(strict_types=1);

namespace Plugins\Analytics\Support;

use Exception;

final class AnalyticsImportException extends Exception
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct(implode(' ', $errors));
    }
}
