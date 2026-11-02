<?php

namespace Plugins\Equipments\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Plugins\Equipments\Models\Equipment;
use Plugins\Equipments\Policies\EquipmentPolicy;

class EquipmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadRoutes();
    }

    public function boot(): void
    {
        $this->loadViews();
        $this->loadMigrations();

        Gate::policy(Equipment::class, EquipmentPolicy::class);
    }

    protected function loadRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        Route::middleware(['web', 'auth'])
            ->prefix('admin/equipments')
            ->name('admin.equipments.')
            ->group(__DIR__.'/../Routes/web.php');
    }

    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'equipments');
    }

    protected function loadMigrations(): void
    {
        if (is_dir(__DIR__.'/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        }
    }
}
