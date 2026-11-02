<?php

use App\Plugins\Plugin;
use App\Plugins\PluginRepository;

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
