<?php

namespace App\Console\Commands;

use App\Plugins\Exceptions\PluginInstallException;
use App\Plugins\PluginInstaller;
use Illuminate\Console\Command;

class PluginInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:install {file : Path to the plugin ZIP archive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install a plugin from a ZIP archive';

    /**
     * Execute the console command.
     */
    public function handle(PluginInstaller $installer): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->error("Archive [{$file}] was not found.");

            return self::FAILURE;
        }

        try {
            $plugin = $installer->install($file);
        } catch (PluginInstallException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Plugin [{$plugin->getAlias()}] installed at {$plugin->getPath()}.");
        $this->components->info('Enable it with: php artisan plugin:enable '.$plugin->getAlias());

        return self::SUCCESS;
    }
}
