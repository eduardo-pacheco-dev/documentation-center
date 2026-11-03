<?php

namespace Plugins\Analytics\Exceptions;

use RuntimeException;

/**
 * Thrown when the user cancels an import while it is being processed.
 */
class AnalyticsImportCanceledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('A importação foi cancelada.');
    }
}
