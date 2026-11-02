<?php

namespace App\Plugins\Exceptions;

use RuntimeException;
use Throwable;

class PluginRemovalException extends RuntimeException
{
    public static function outsidePluginsPath(string $path): self
    {
        return new self("O plugin em {$path} não está dentro do diretório de plugins e não pode ser removido.");
    }

    public static function couldNotDelete(string $path): self
    {
        return new self("Não foi possível apagar {$path}. Verifique as permissões do diretório.");
    }

    public static function uninstallFailed(string $alias, Throwable $previous): self
    {
        return new self(
            "Não foi possível executar a desinstalação do plugin [{$alias}]: {$previous->getMessage()}",
            0,
            $previous,
        );
    }
}
