<?php

namespace Plugins\Backups\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackupsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadRoutes();
    }

    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();
    }

    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth', 'admin'])
            ->prefix('admin/backups')
            ->name('admin.backups.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'backups');
    }

    protected function loadMigrations(): void
    {
        if (is_dir(__DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }
    }
}
