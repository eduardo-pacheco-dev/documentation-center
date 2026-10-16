<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('unlock', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // End-to-end tests run against the compiled assets: the Vite development
        // server compiles the whole Tailwind stylesheet on its first request,
        // which is far too slow for the first page load. Pointing Vite at a hot
        // file that never exists makes it fall back to the build manifest.
        if ($this->app->environment('cypress')) {
            Vite::useHotFile(storage_path('framework/vite.hot'));
        }
    }
}
