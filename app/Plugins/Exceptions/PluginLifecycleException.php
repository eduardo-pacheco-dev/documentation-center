<?php

namespace App\Plugins\Exceptions;

use App\Plugins\Contracts\PluginLifecycle;
use RuntimeException;

class PluginLifecycleException extends RuntimeException
{
    public static function missingClass(string $alias, string $class): self
    {
        return new self("A classe de ciclo de vida [{$class}] declarada pelo plugin [{$alias}] não existe.");
    }

    public static function invalidClass(string $alias, string $class): self
    {
        return new self(sprintf(
            'A classe de ciclo de vida [%s] declarada pelo plugin [%s] precisa implementar %s.',
            $class,
            $alias,
            PluginLifecycle::class,
        ));
    }

    public static function invalidDeclaration(string $alias): self
    {
        return new self("O atributo \"lifecycle\" do plugin [{$alias}] precisa ser o nome de uma classe.");
    }
}
