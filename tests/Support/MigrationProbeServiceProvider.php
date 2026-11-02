<?php

namespace Tests\Support;

use Illuminate\Support\ServiceProvider;

/**
 * Marks that a plugin provider was registered before its migrations ran.
 */
class MigrationProbeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config(['plugins.provider_probe' => true]);
    }
}
