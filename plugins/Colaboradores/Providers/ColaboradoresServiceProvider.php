<?php

namespace Plugins\Colaboradores\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Colaboradores\Models\Colaborador;
use Plugins\Colaboradores\Policies\ColaboradorPolicy;

class ColaboradoresServiceProvider extends ServiceProvider
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

        Gate::policy(Colaborador::class, ColaboradorPolicy::class);
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
            ->prefix('admin/colaboradores')
            ->name('admin.colaboradores.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    /**
     * Load the plugin views under the plugin alias namespace.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'colaboradores');
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
