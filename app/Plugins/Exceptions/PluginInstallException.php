<?php

namespace App\Plugins\Exceptions;

use RuntimeException;

class PluginInstallException extends RuntimeException
{
    public static function invalidArchive(): self
    {
        return new self('O arquivo enviado não é um ZIP válido.');
    }

    public static function zipUnsupported(): self
    {
        return new self('A extensão ZIP do PHP não está disponível neste servidor.');
    }

    public static function unsafeEntry(string $entry): self
    {
        return new self("O arquivo contém um caminho inseguro ({$entry}) e não pode ser instalado.");
    }

    public static function missingManifest(): self
    {
        return new self('O arquivo não contém um plugin.json válido.');
    }

    public static function multiplePlugins(): self
    {
        return new self('O arquivo contém mais de um plugin. Envie um plugin por vez.');
    }

    public static function invalidAlias(): self
    {
        return new self('O plugin.json precisa de um "alias" em kebab-case (ex.: meu-plugin).');
    }

    public static function alreadyInstalled(string $alias): self
    {
        return new self("Já existe um plugin com o alias [{$alias}] instalado.");
    }

    public static function couldNotExtract(): self
    {
        return new self('Não foi possível extrair o conteúdo do arquivo enviado.');
    }

    public static function couldNotMove(string $path): self
    {
        return new self("Não foi possível gravar o plugin em {$path}. Verifique as permissões do diretório.");
    }
}
