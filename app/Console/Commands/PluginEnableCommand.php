<?php

namespace App\Console\Commands;

use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Console\Command;

class PluginEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:enable {alias : The plugin alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the given plugin';

    /**
     * Execute the console command.
     */
    public function handle(PluginRepository $plugins, PluginMigrator $migrator): int
    {
        try {
            $plugin = $plugins->findOrFail((string) $this->argument('alias'));
        } catch (PluginNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($plugin->enabled()) {
            $this->components->info("Plugin [{$plugin->getAlias()}] is already enabled.");

            return self::SUCCESS;
        }

        $plugin->enable();

        $plugins->clear();

        try {
            $migrations = $migrator->migrate($plugin);
        } catch (\Throwable $exception) {
            $plugin->disable();
            $plugins->clear();
            $this->callSilently('route:clear');

            $this->error("Plugin [{$plugin->getAlias()}] migrations failed: {$exception->getMessage()}");

            return self::FAILURE;
        }

        try {
            $plugin->install();
        } catch (\Throwable $exception) {
            $plugin->disable();
            $plugins->clear();
            $this->callSilently('route:clear');

            $this->error("Plugin [{$plugin->getAlias()}] install hook failed: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $this->callSilently('route:clear');

        $this->components->info("Plugin [{$plugin->getAlias()}] enabled.");

        if ($migrations !== []) {
            $this->components->info(count($migrations).' migration(s) executed.');
        }

        return self::SUCCESS;
    }
}
