<?php

namespace App\Console\Commands;

use App\Plugins\Exceptions\PluginDependencyException;
use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\PluginRepository;
use Illuminate\Console\Command;

class PluginDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:disable {alias : The plugin alias}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the given plugin';

    /**
     * Execute the console command.
     */
    public function handle(PluginRepository $plugins): int
    {
        try {
            $plugin = $plugins->findOrFail((string) $this->argument('alias'));
        } catch (PluginNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($plugin->disabled()) {
            $this->components->info("Plugin [{$plugin->getAlias()}] is already disabled.");

            return self::SUCCESS;
        }

        try {
            $plugin->disable();
        } catch (PluginDependencyException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $plugins->clear();
        $this->callSilently('route:clear');

        $this->components->info("Plugin [{$plugin->getAlias()}] disabled.");

        return self::SUCCESS;
    }
}
