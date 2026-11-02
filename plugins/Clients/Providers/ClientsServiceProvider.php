<?php

namespace Plugins\Clients\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Clients\Models\Client;
use Plugins\Clients\Policies\ClientPolicy;

class ClientsServiceProvider extends ServiceProvider
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

        Gate::policy(Client::class, ClientPolicy::class);
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
            ->prefix('admin/clients')
            ->name('admin.clients.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    /**
     * Load the plugin views under the plugin alias namespace.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'clients');
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
