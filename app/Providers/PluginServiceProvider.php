<?php

namespace App\Providers;

use App\Plugins\Plugin;
use App\Plugins\PluginActivator;
use App\Plugins\PluginRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Boots the plugin system.
 *
 * Follows the Akaunting module lifecycle: every enabled plugin is a mini
 * Laravel app whose service providers, aliases, files and translations are
 * registered before the framework boots.
 */
class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services and every enabled plugin.
     */
    public function register(): void
    {
        $this->app->singleton(PluginActivator::class, fn (Application $app): PluginActivator => new PluginActivator(
            $app['files'],
            $app['config'],
            $app,
        ));

        $this->app->singleton(PluginRepository::class, fn (Application $app): PluginRepository => new PluginRepository(
            $app,
            $app['files'],
            (string) $app['config']->get('plugins.paths.plugins'),
        ));

        foreach ($this->app->make(PluginRepository::class)->enabled() as $plugin) {
            $this->registerPlugin($plugin);
        }
    }

    /**
     * Boot every enabled plugin.
     */
    public function boot(): void
    {
        foreach ($this->app->make(PluginRepository::class)->enabled() as $plugin) {
            $this->bootPlugin($plugin);
        }
    }

    /**
     * Register the plugin providers, aliases and files.
     *
     * Called by the plugin loader for every enabled plugin and by the
     * migrator before running a plugin's migrations, so migrations can rely
     * on the plugin configuration and container bindings being available.
     */
    public function registerPlugin(Plugin $plugin): void
    {
        if (config('plugins.autoload.composer') === 'register') {
            $this->requireComposerAutoload($plugin);
        }

        if (config('plugins.autoload.files') === 'register') {
            $this->requireFiles($plugin);
        }

        foreach ($plugin->aliases() as $class => $alias) {
            if (is_string($class) && is_string($alias) && ! class_exists($alias, false)) {
                class_alias($class, $alias);
            }
        }

        foreach ($plugin->providers() as $provider) {
            if (! class_exists($provider)) {
                $this->app['log']->warning('Plugin declares an unknown service provider.', [
                    'plugin' => $plugin->getAlias(),
                    'provider' => $provider,
                ]);

                continue;
            }

            $this->app->register($provider);
        }
    }

    /**
     * Boot the plugin translations and remaining autoload stages.
     */
    private function bootPlugin(Plugin $plugin): void
    {
        if (config('plugins.autoload.translations') === true) {
            $langPath = $plugin->getExtraPath('Resources/lang');

            if (is_dir($langPath)) {
                $this->app['translator']->addNamespace($plugin->getAlias(), $langPath);
            }
        }

        if (config('plugins.autoload.files') === 'boot') {
            $this->requireFiles($plugin);
        }

        if (config('plugins.autoload.composer') === 'boot') {
            $this->requireComposerAutoload($plugin);
        }

        $this->app['events']->dispatch(sprintf('plugin.%s.boot', $plugin->getAlias()), [$plugin]);
    }

    /**
     * Include the plugin level composer autoloader, when present.
     */
    private function requireComposerAutoload(Plugin $plugin): void
    {
        $autoload = $plugin->getExtraPath('vendor/autoload.php');

        if (is_file($autoload)) {
            require_once $autoload;
        }
    }

    /**
     * Include the files declared by the plugin manifest.
     */
    private function requireFiles(Plugin $plugin): void
    {
        foreach ($plugin->files() as $file) {
            $path = $plugin->getExtraPath($file);

            if (is_file($path)) {
                require_once $path;
            }
        }
    }
}
