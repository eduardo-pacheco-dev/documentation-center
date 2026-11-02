<?php

namespace App\Plugins;

use Illuminate\Database\Migrations\Migrator;

/**
 * Runs the migrations shipped inside a plugin.
 */
class PluginMigrator
{
    public function __construct(private readonly Migrator $migrator) {}

    /**
     * Run the plugin migrations and return the executed migration names.
     *
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
        $path = $plugin->getExtraPath('Database/Migrations');

        if (! is_dir($path)) {
            return [];
        }

        if (! $this->migrator->repositoryExists()) {
            $this->migrator->getRepository()->createRepository();
        }

        return $this->migrator->run([$path], ['pretend' => false]);
    }
}
