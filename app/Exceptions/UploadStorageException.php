<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class UploadStorageException extends RuntimeException
{
    /**
     * @param  string  $fieldId  Field whose upload could not be stored.
     */
    public function __construct(
        public readonly string $fieldId,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
