<?php

namespace App\Console\Commands;

use App\Plugins\Exceptions\PluginNotFoundException;
use App\Plugins\Exceptions\PluginRemovalException;
use App\Plugins\PluginRemover;
use App\Plugins\PluginRepository;
use Illuminate\Console\Command;

class PluginRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:remove
                            {alias : The plugin alias}
                            {--force : Remove without asking for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the given plugin and delete its files';

    /**
     * Execute the console command.
     */
    public function handle(PluginRepository $plugins, PluginRemover $remover): int
    {
        try {
            $plugin = $plugins->findOrFail((string) $this->argument('alias'));
        } catch (PluginNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Remove plugin [{$plugin->getAlias()}]? Its files will be deleted.", false)) {
            $this->components->info('Removal aborted.');

            return self::SUCCESS;
        }

        try {
            $remover->remove($plugin->getAlias());
        } catch (PluginRemovalException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->getAlias()}] removed.");

        return self::SUCCESS;
    }
}
