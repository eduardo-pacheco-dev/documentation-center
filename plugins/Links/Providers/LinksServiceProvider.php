<?php

namespace Plugins\Links\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Links\Models\ShortLink;
use Plugins\Links\Policies\ShortLinkPolicy;

class LinksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadRoutes();
    }

    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();

        Gate::policy(ShortLink::class, ShortLinkPolicy::class);
    }

    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/../Routes/web.php');

        Route::middleware('web')
            ->prefix('s')
            ->name('public.short-links.')
            ->group(__DIR__.'/../Routes/public.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1')
            ->name('api.v1.')
            ->group(__DIR__.'/../Routes/api.php');
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'links');
    }

    protected function loadMigrations(): void
    {
        if (is_dir(__DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }
    }
}
