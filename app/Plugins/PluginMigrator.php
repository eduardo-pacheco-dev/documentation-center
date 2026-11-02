<?php

namespace App\Plugins;

use App\Providers\PluginServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;

/**
 * Runs the migrations shipped inside a plugin.
 */
class PluginMigrator
{
    public function __construct(
        private readonly Migrator $migrator,
        private readonly Application $app,
    ) {}

    /**
     * Run the plugin migrations and return the executed migration names.
     *
     * The plugin providers are registered first so migrations can use the
     * plugin configuration (for example a dedicated database connection).
     * Migrations are tracked by the application migrations table. Plugins
     * that declare their own connection (like Analytics) write the tables on
     * that connection through their migration files.
     *
     * @return list<string>
     *
     * @throws \Throwable
     */
    public function migrate(Plugin $plugin): array
    {
        $this->registerProviders($plugin);

        $path = $plugin->getExtraPath('Database/Migrations');

        if (! is_dir($path)) {
            return [];
        }

        if (! $this->migrator->repositoryExists()) {
            $this->migrator->getRepository()->createRepository();
        }

        return $this->migrator->run([$path], ['pretend' => false]);
    }

    /**
     * Register the providers declared by the plugin in the current process.
     */
    private function registerProviders(Plugin $plugin): void
    {
        $providers = $this->app->getProviders(PluginServiceProvider::class);

        $provider = reset($providers);

        if ($provider instanceof PluginServiceProvider) {
            $provider->registerPlugin($plugin);
        }
    }
}
