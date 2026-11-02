<?php

namespace App\Plugins\Contracts;

/**
 * Lifecycle hooks a plugin may declare in its manifest.
 *
 * The manifest points to the implementing class through the "lifecycle"
 * attribute. Install runs after the plugin is enabled and its migrations
 * have been executed; uninstall runs before its files are deleted.
 */
interface PluginLifecycle
{
    /**
     * Prepare the plugin data after it is enabled for the first time.
     */
    public function install(): void;

    /**
     * Remove the plugin data before its files are deleted.
     */
    public function uninstall(): void;
}
