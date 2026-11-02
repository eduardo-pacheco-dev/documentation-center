<?php

namespace App\Console\Commands;

use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use Illuminate\Console\Command;

class PluginListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:list
                            {--only=all : Filter the listing: all, enabled or disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List every discovered plugin and its status';

    /**
     * Execute the console command.
     */
    public function handle(PluginRepository $plugins): int
    {
        $only = (string) $this->option('only');

        if (! in_array($only, ['all', 'enabled', 'disabled'], true)) {
            $this->error('The --only option must be one of: all, enabled, disabled.');

            return self::FAILURE;
        }

        $items = match ($only) {
            'enabled' => $plugins->enabled(),
            'disabled' => $plugins->disabled(),
            default => $plugins->all(),
        };

        if ($items->isEmpty()) {
            $this->components->info('No plugins found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Alias', 'Name', 'Version', 'Status', 'Providers', 'Path'],
            $items->map(fn (Plugin $plugin): array => [
                $plugin->getAlias(),
                $plugin->getName(),
                $plugin->getVersion() ?? '-',
                $plugin->enabled() ? 'Enabled' : 'Disabled',
                count($plugin->providers()),
                $plugin->getPath(),
            ])->all(),
        );

        return self::SUCCESS;
    }
}
