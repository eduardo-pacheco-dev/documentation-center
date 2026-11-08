<?php

namespace Plugins\Agenda\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Agenda\Console\DispatchAgendaRemindersCommand;
use Plugins\Agenda\Models\AgendaCalendar;
use Plugins\Agenda\Models\AgendaEvent;
use Plugins\Agenda\Policies\AgendaCalendarPolicy;
use Plugins\Agenda\Policies\AgendaEventPolicy;

class AgendaServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'agenda');

        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([DispatchAgendaRemindersCommand::class]);
        }
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

        Gate::policy(AgendaCalendar::class, AgendaCalendarPolicy::class);
        Gate::policy(AgendaEvent::class, AgendaEventPolicy::class);
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
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'agenda');
    }

    /**
     * Load the plugin translations under the plugin alias namespace.
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'agenda');
    }
}
