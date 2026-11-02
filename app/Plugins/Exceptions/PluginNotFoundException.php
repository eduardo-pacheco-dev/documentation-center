<?php

namespace App\Plugins\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PluginNotFoundException extends NotFoundHttpException
{
    public static function named(string $alias): self
    {
        return new self("Plugin [{$alias}] was not found.");
    }
}
