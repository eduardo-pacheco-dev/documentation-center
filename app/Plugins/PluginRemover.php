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
        private readonly PluginDependencies $dependencies,
    ) {}

    /**
     * Remove the plugin with the given alias from disk.
     *
     * @throws PluginRemovalException
     */
    public function remove(string $alias): void
    {
        $plugin = $this->plugins->findOrFail($alias);

        $this->dependencies->assertCanRemove($plugin);

        $path = realpath($plugin->getPath());
        $root = realpath(rtrim((string) config('plugins.paths.plugins'), '/'));

        if ($path === false || $root === false || ! $this->isInside($path, $root)) {
            throw PluginRemovalException::outsidePluginsPath($plugin->getPath());
        }

        try {
            $plugin->uninstall();
        } catch (\Throwable $exception) {
            throw PluginRemovalException::uninstallFailed($plugin->getAlias(), $exception);
        }

        $this->app->make(PluginActivator::class)->delete($plugin);

        if (! $this->files->deleteDirectory($path)) {
            throw PluginRemovalException::couldNotDelete($path);
        }

        // Windows can briefly lock the directory after its files are removed.
        for ($attempt = 0; $this->files->isDirectory($path) && $attempt < 2; $attempt++) {
            usleep(100_000);
            clearstatcache();
            $this->files->deleteDirectory($path);
        }

        if ($this->files->isDirectory($path)) {
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
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');

        return $path !== $root && str_starts_with($path.'/', $root.'/');
    }
}
