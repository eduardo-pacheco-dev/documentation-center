<?php

use App\Notifications\PluginNotifier;
use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use App\Plugins\PluginStorage;

if (! function_exists('plugin')) {
    /**
     * Resolve the plugin repository, or a single plugin by alias.
     */
    function plugin(?string $alias = null): PluginRepository|Plugin|null
    {
        $repository = app(PluginRepository::class);

        return $alias === null ? $repository : $repository->get($alias);
    }
}

if (! function_exists('plugin_attribute')) {
    /**
     * Read an attribute from a plugin manifest.
     */
    function plugin_attribute(string $alias, string $attribute, mixed $default = null): mixed
    {
        return plugin($alias)?->get($attribute, $default);
    }
}

if (! function_exists('plugin_version')) {
    /**
     * Read the version of a plugin.
     */
    function plugin_version(string $alias): ?string
    {
        return plugin($alias)?->getVersion();
    }
}

if (! function_exists('plugin_path')) {
    /**
     * Build a path inside a plugin directory.
     */
    function plugin_path(string $alias, string $path = ''): string
    {
        $plugin = plugin($alias);

        if ($plugin === null) {
            throw new InvalidArgumentException("Plugin [{$alias}] was not found.");
        }

        return $path === '' ? $plugin->getPath() : $plugin->getExtraPath($path);
    }
}

if (! function_exists('plugin_storage')) {
    /**
     * Resolve the storage sandbox of a plugin.
     */
    function plugin_storage(Plugin|string $plugin): PluginStorage
    {
        return PluginStorage::for($plugin);
    }
}

if (! function_exists('plugin_notifier')) {
    /**
     * Resolve the notification sender of a plugin.
     */
    function plugin_notifier(Plugin|string $plugin): PluginNotifier
    {
        return PluginNotifier::for($plugin instanceof Plugin ? $plugin->getAlias() : $plugin);
    }
}
