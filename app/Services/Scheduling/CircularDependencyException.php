<?php

namespace App\Services\Scheduling;

use RuntimeException;

/**
 * Raised when the dependencies of a project form a loop.
 */
class CircularDependencyException extends RuntimeException
{
    /**
     * Build the exception from the tasks that could not be ordered.
     *
     * @param  list<string>  $taskNames
     */
    public static function fromTasks(array $taskNames): self
    {
        $names = implode(', ', array_slice($taskNames, 0, 5));

        return new self("As dependências formam um ciclo envolvendo: {$names}.");
    }
}
