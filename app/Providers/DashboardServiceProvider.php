<?php

namespace App\Providers;

use App\Dashboard\WidgetRegistry;
use App\Dashboard\Widgets\ActivePluginsWidget;
use App\Dashboard\Widgets\InstalledPluginsWidget;
use App\Dashboard\Widgets\RecentUsersWidget;
use App\Dashboard\Widgets\UserCountWidget;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the dashboard widget registry and the widgets shipped with the core.
 */
class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register the widget registry.
     */
    public function register(): void
    {
        $this->app->singleton(WidgetRegistry::class);
    }

    /**
     * Register the core dashboard widgets.
     */
    public function boot(): void
    {
        $this->app->make(WidgetRegistry::class)->registerMany([
            $this->app->make(UserCountWidget::class),
            $this->app->make(InstalledPluginsWidget::class),
            $this->app->make(ActivePluginsWidget::class),
            $this->app->make(RecentUsersWidget::class),
        ]);
    }
}
