<?php

namespace Plugins\Example\Providers;

use App\Dashboard\WidgetRegistry;
use App\Navigation\NavigationItem;
use App\Navigation\NavigationRegistry;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Example\Widgets\ExampleWidget;

class ExampleServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'example');

        $this->loadRoutes();
    }

    /**
     * Bootstrap the plugin services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'example');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'example');

        $this->app->make(WidgetRegistry::class)->register(new ExampleWidget);

        $this->app->make(NavigationRegistry::class)->register(
            NavigationItem::make('example.index', 'Exemplo de navegação', 'plugins.example.index')
                ->icon('cube')
                ->inModules()
                ->order(100),
        );
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
            ->prefix('plugins/example')
            ->name('plugins.example.')
            ->group(__DIR__.'/../Routes/web.php');
    }
}
