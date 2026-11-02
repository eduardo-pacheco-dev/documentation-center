<?php

namespace Plugins\Erbs\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Erbs\Models\Erb;
use Plugins\Erbs\Policies\ErbPolicy;

class ErbsServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services.
     */
    public function register(): void
    {
        $this->loadRoutes();
    }

    /**
     * Bootstrap the plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();

        Gate::policy(Erb::class, ErbPolicy::class);
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
            ->prefix('admin/erbs')
            ->name('admin.erbs.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    /**
     * Load the plugin views under the plugin alias namespace.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'erbs');
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
