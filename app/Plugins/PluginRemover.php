<?php

namespace App\Plugins;

use App\Plugins\Exceptions\PluginRemovalException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

/**
 * Removes an installed plugin and forgets its stored status.
 */
class PluginRemover
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly PluginRepository $plugins,
    ) {}

    /**
     * Remove the plugin with the given alias from disk.
     *
     * @throws PluginRemovalException
     */
    public function remove(string $alias): void
    {
        $plugin = $this->plugins->findOrFail($alias);

        $path = realpath($plugin->getPath());
        $root = realpath(rtrim((string) config('plugins.paths.plugins'), '/'));

        if ($path === false || $root === false || ! $this->isInside($path, $root)) {
            throw PluginRemovalException::outsidePluginsPath($plugin->getPath());
        }

        $this->app->make(PluginActivator::class)->delete($plugin);

        if (! $this->files->deleteDirectory($path)) {
            throw PluginRemovalException::couldNotDelete($path);
        }

        $this->plugins->clear();

        $this->app['events']->dispatch(sprintf('plugin.%s.removed', $plugin->getAlias()), [$plugin]);
    }

    /**
     * Determine whether the given path lives inside the plugins root.
     */
    private function isInside(string $path, string $root): bool
    {
        return $path !== $root && str_starts_with($path.'/', rtrim($root, '/').'/');
    }
}
