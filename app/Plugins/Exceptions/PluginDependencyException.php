<?php

namespace App\Plugins\Exceptions;

use RuntimeException;

class PluginDependencyException extends RuntimeException
{
    /**
     * The plugin cannot be enabled because its dependencies are unavailable.
     *
     * @param  list<string>  $missing
     */
    public static function missing(string $alias, array $missing): self
    {
        return new self(sprintf(
            'O plugin [%s] requer os plugins habilitados: %s.',
            $alias,
            implode(', ', $missing),
        ));
    }

    /**
     * The plugin cannot be disabled because other enabled plugins require it.
     *
     * @param  list<string>  $dependents
     */
    public static function requiredByDisabled(string $alias, array $dependents): self
    {
        return new self(sprintf(
            'O plugin [%s] não pode ser desabilitado porque é requerido por: %s.',
            $alias,
            implode(', ', $dependents),
        ));
    }

    /**
     * The plugin cannot be removed because other enabled plugins require it.
     *
     * @param  list<string>  $dependents
     */
    public static function requiredByRemoved(string $alias, array $dependents): self
    {
        return new self(sprintf(
            'O plugin [%s] não pode ser removido porque é requerido por: %s.',
            $alias,
            implode(', ', $dependents),
        ));
    }
}
