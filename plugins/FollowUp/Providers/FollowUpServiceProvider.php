<?php

namespace Plugins\FollowUp\Providers;

use App\Dashboard\WidgetRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Plugins\FollowUp\Console\SendDailySummaryCommand;
use Plugins\FollowUp\Models\FollowUpSetting;
use Plugins\FollowUp\Widgets\OverdueFollowUpsWidget;
use Throwable;

class FollowUpServiceProvider extends ServiceProvider
{
    /**
     * Register the plugin services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'follow-up');

        $this->loadRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([SendDailySummaryCommand::class]);
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

        $this->app->make(WidgetRegistry::class)->register(new OverdueFollowUpsWidget);

        $this->applySettings();
        $this->scheduleDailySummary();
    }

    /**
     * Override the plugin defaults with the values saved in the settings page.
     */
    protected function applySettings(): void
    {
        try {
            if (! Schema::hasTable('follow_up_settings')) {
                return;
            }

            $settings = FollowUpSetting::current();
        } catch (Throwable) {
            return;
        }

        config([
            'follow-up.per_page' => $settings->per_page,
            'follow-up.due_soon_days' => $settings->due_soon_days,
            'follow-up.widget_limit' => $settings->widget_limit,
            'follow-up.email.subject_prefix' => $settings->email_subject_prefix,
            'follow-up.email.summary_times' => $settings->summaryTimes(),
            'follow-up.email.summary_days' => $settings->summaryDays(),
            'follow-up.email.summary_enabled' => $settings->email_summary_enabled,
        ]);

        $this->applyMailSettings($settings);
    }

    /**
     * Use the SMTP credentials saved on the settings page when enabled.
     */
    protected function applyMailSettings(FollowUpSetting $settings): void
    {
        if (! $settings->mail_enabled || blank($settings->mail_host)) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings->mail_host,
            'mail.mailers.smtp.port' => $settings->mail_port,
            'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: null,
            'mail.mailers.smtp.username' => $settings->mail_username,
            'mail.mailers.smtp.password' => $settings->mail_password,
        ]);

        if (filled($settings->mail_from_address)) {
            config(['mail.from.address' => $settings->mail_from_address]);
        }

        if (filled($settings->mail_from_name)) {
            config(['mail.from.name' => $settings->mail_from_name]);
        }
    }

    /**
     * Schedule the periodic summary e-mail for each configured time.
     */
    protected function scheduleDailySummary(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! config('follow-up.email.summary_enabled', true)) {
            return;
        }

        $times = (array) config('follow-up.email.summary_times', ['08:00', '17:00']);
        $days = (array) config('follow-up.email.summary_days', range(0, 6));

        foreach ($times as $time) {
            Schedule::command('follow-up:send-daily-summary')->dailyAt($time)->days($days);
        }
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
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'follow-up');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views', 'follow-up');
    }

    /**
     * Load the plugin translations under the plugin alias namespace.
     */
    protected function loadTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'follow-up');
    }
}
