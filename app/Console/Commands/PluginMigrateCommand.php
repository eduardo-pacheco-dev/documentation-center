<?php

namespace App\Console\Commands;

use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Console\Command;

class PluginMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:migrate {alias? : The plugin alias, or every enabled plugin when omitted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migrations shipped inside enabled plugins';

    /**
     * Execute the console command.
     */
    public function handle(PluginRepository $plugins, PluginMigrator $migrator): int
    {
        $alias = $this->argument('alias');

        if ($alias !== null) {
            try {
                $targets = collect([$plugins->findOrFail((string) $alias)]);
            } catch (PluginNotFoundException $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }
        } else {
            $targets = $plugins->enabled();
        }

        if ($targets->isEmpty()) {
            $this->components->info('No plugins to migrate.');

            return self::SUCCESS;
        }

        $failed = false;

        foreach ($targets as $plugin) {
            try {
                $migrations = $migrator->migrate($plugin);
            } catch (\Throwable $exception) {
                $this->error("Plugin [{$plugin->getAlias()}] migrations failed: {$exception->getMessage()}");

                $failed = true;

                continue;
            }

            $this->components->info(sprintf(
                'Plugin [%s]: %d migration(s) executed.',
                $plugin->getAlias(),
                count($migrations),
            ));
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
