<?php

namespace Plugins\Analytics\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Analytics\Console\SnapshotCommand;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'analytics');

        $this->loadRoutes();
    }

    /**
     * Bootstrap the plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();

        if ($this->app->runningInConsole()) {
            $this->commands([
                SnapshotCommand::class,
            ]);
        }
    }

    /**
     * Load the plugin routes inside the authenticated admin area.
     */
    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth'])
            ->prefix('admin/analytics')
            ->name('analytics.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    /**
     * Load the plugin views under the plugin alias namespace.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'analytics');
    }

    /**
     * Load the plugin translations under the plugin alias namespace.
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'analytics');
    }

    /**
     * Load the plugin migrations.
     */
    protected function loadMigrations(): void
    {
        if (is_dir(__DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }
    }
}
