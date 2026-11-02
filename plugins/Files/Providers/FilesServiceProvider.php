<?php

namespace Plugins\Files\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Files\Models\Document;
use Plugins\Files\Models\Folder;
use Plugins\Files\Policies\DocumentPolicy;
use Plugins\Files\Policies\FolderPolicy;

class FilesServiceProvider extends ServiceProvider
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

        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Folder::class, FolderPolicy::class);
    }

    /**
     * Load the plugin routes inside the authenticated admin and API areas.
     */
    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__.'/../Routes/web.php');

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/v1')
            ->name('api.v1.')
            ->group(__DIR__.'/../Routes/api.php');
    }

    /**
     * Load the plugin views under the plugin alias namespace.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'files');
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
