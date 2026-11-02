<?php

namespace App\Plugins;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;

/**
 * Persists the enabled/disabled status of every plugin.
 *
 * The state lives in a JSON file on the storage disk (Akaunting's "file
 * activator" concept), so it survives deploys without touching versioned
 * plugin manifests.
 */
class PluginActivator
{
    /**
     * @var array<string, bool>|null
     */
    private ?array $statuses = null;

    public function __construct(
        private readonly Filesystem $files,
        private readonly Config $config,
        private readonly Container $app,
    ) {}

    /**
     * Whether the given plugin matches the requested status.
     */
    public function is(Plugin $plugin, bool $active): bool
    {
        $statuses = $this->statuses();

        if (! array_key_exists($plugin->getAlias(), $statuses)) {
            $statuses[$plugin->getAlias()] = (bool) $plugin->get('active', false);
        }

        return $statuses[$plugin->getAlias()] === $active;
    }

    /**
     * Enable the given plugin.
     */
    public function enable(Plugin $plugin): void
    {
        $this->setActive($plugin, true);
    }

    /**
     * Disable the given plugin.
     */
    public function disable(Plugin $plugin): void
    {
        $this->setActive($plugin, false);
    }

    /**
     * Store the status of the given plugin.
     */
    public function setActive(Plugin $plugin, bool $active): void
    {
        $statuses = $this->statuses();
        $statuses[$plugin->getAlias()] = $active;

        $this->statuses = $statuses;

        $this->write();
    }

    /**
     * Forget the stored status of the given plugin.
     */
    public function delete(Plugin $plugin): void
    {
        $statuses = $this->statuses();

        if (! array_key_exists($plugin->getAlias(), $statuses)) {
            return;
        }

        unset($statuses[$plugin->getAlias()]);

        $this->statuses = $statuses;

        $this->write();
    }

    /**
     * Remove the status file entirely.
     */
    public function reset(): void
    {
        if ($this->files->exists($this->getFilePath())) {
            $this->files->delete($this->getFilePath());
        }

        $this->statuses = [];

        $this->flushCache();
    }

    /**
     * Every stored status, keyed by plugin alias.
     *
     * @return array<string, bool>
     */
    public function statuses(): array
    {
        return $this->statuses ??= $this->read();
    }

    /**
     * The path of the status file.
     */
    public function getFilePath(): string
    {
        return (string) $this->config->get('plugins.statuses', storage_path('plugin_statuses.json'));
    }

    /**
     * Read the statuses from disk.
     *
     * @return array<string, bool>
     */
    public function read(): array
    {
        if (! $this->config->get('plugins.cache.enabled')) {
            return $this->readFromDisk();
        }

        return $this->app->make(CacheManager::class)->remember(
            $this->config->get('plugins.cache.key').'.statuses',
            $this->config->get('plugins.cache.lifetime'),
            fn (): array => $this->readFromDisk(),
        );
    }

    /**
     * Persist the statuses to disk.
     */
    private function write(): void
    {
        $path = $this->getFilePath();

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path,
            json_encode($this->statuses(), JSON_PRETTY_PRINT).PHP_EOL,
        );

        $this->matchDirectoryOwnership($path);

        $this->flushCache();
    }

    /**
     * Keep the status file writable by the web process when the CLI runs as root.
     */
    private function matchDirectoryOwnership(string $path): void
    {
        if (! function_exists('posix_geteuid') || posix_geteuid() !== 0) {
            return;
        }

        $directory = dirname($path);
        $owner = @fileowner($directory);
        $group = @filegroup($directory);

        if ($owner === false || $group === false) {
            return;
        }

        @chown($path, $owner);
        @chgrp($path, $group);
        @chmod($path, 0664);
    }

    /**
     * @return array<string, bool>
     */
    private function readFromDisk(): array
    {
        $path = $this->getFilePath();

        if (! $this->files->exists($path)) {
            return [];
        }

        $decoded = json_decode((string) $this->files->get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_map(fn (mixed $status): bool => (bool) $status, $decoded);
    }

    private function flushCache(): void
    {
        if (! $this->config->get('plugins.cache.enabled')) {
            return;
        }

        $this->app->make(CacheManager::class)->forget($this->config->get('plugins.cache.key').'.statuses');
    }
}
