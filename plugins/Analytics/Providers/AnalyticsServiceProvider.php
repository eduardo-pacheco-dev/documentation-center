<?php

declare(strict_types=1);

namespace Plugins\Analytics\Providers;

use App\Dashboard\WidgetRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Analytics\Widgets\AnalyticsOverviewWidget;
use Plugins\Analytics\Widgets\AnalyticsSupplierWidget;

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

        if (is_dir(__DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }

        $this->app->make(WidgetRegistry::class)->registerMany([
            new AnalyticsOverviewWidget,
            new AnalyticsSupplierWidget,
        ]);
    }

    /**
     * Load the plugin routes.
     */
    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware('web')
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
}
