<?php

namespace Plugins\Forms\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Plugins\Forms\Models\Form;
use Plugins\Forms\Models\FormTemplate;
use Plugins\Forms\Policies\FormPolicy;
use Plugins\Forms\Policies\FormTemplatePolicy;

class FormsServiceProvider extends ServiceProvider
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
        $this->loadComponents();
        $this->loadMigrations();

        Gate::policy(Form::class, FormPolicy::class);
        Gate::policy(FormTemplate::class, FormTemplatePolicy::class);

        $this->registerUploadErrorHandling();
    }

    /**
     * Load the plugin routes for the admin, public and API areas.
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

        Route::middleware(['web', 'signed'])
            ->prefix('formulario')
            ->name('public.forms.')
            ->group(__DIR__.'/../Routes/public.php');

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
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'forms');
    }

    /**
     * Register the plugin anonymous components so the plugin ships its own UI.
     */
    protected function loadComponents(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components');
    }

    /**
     * Redirect public submissions above the server upload limit back to the form.
     */
    protected function registerUploadErrorHandling(): void
    {
        $this->app->make(ExceptionHandler::class)->renderable(function (PostTooLargeException $exception, Request $request) {
            $formId = $request->segment(2);

            if (! $request->is('formulario/*') || blank($formId)) {
                return null;
            }

            return redirect()->to(URL::signedRoute('public.forms.fill', [
                'form' => $formId,
                'upload_error' => 1,
            ]));
        });
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
