<?php

namespace App\Settings;

use App\Models\Setting;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemSettings
{
    /**
     * The settings key that stores the system timezone.
     */
    public const TIMEZONE_KEY = 'timezone';

    /**
     * The cache key that stores the resolved system timezone.
     */
    public const TIMEZONE_CACHE_KEY = 'system.timezone';

    /**
     * Resolve the system timezone, falling back to the application default.
     */
    public static function timezone(): string
    {
        $default = self::defaultTimezone();

        try {
            $cached = Cache::get(self::TIMEZONE_CACHE_KEY);

            if (is_string($cached) && self::isValidTimezone($cached)) {
                return $cached;
            }

            if (! Schema::hasTable((new Setting)->getTable())) {
                return $default;
            }

            $timezone = Setting::query()
                ->where('key', self::TIMEZONE_KEY)
                ->value('value');
        } catch (Throwable) {
            return $default;
        }

        if (! is_string($timezone) || ! self::isValidTimezone($timezone)) {
            return $default;
        }

        try {
            Cache::forever(self::TIMEZONE_CACHE_KEY, $timezone);
        } catch (Throwable) {
            // The timezone is still valid for the current request.
        }

        return $timezone;
    }

    /**
     * Persist the system timezone and apply it to the current process.
     */
    public static function setTimezone(string $timezone): void
    {
        Setting::query()->updateOrCreate(
            ['key' => self::TIMEZONE_KEY],
            ['value' => $timezone],
        );

        try {
            Cache::forever(self::TIMEZONE_CACHE_KEY, $timezone);
        } catch (Throwable) {
            // The value is persisted; the next read will resolve it from the database.
        }

        self::apply($timezone);
    }

    /**
     * Apply the timezone to the configuration and PHP runtime.
     */
    public static function apply(string $timezone): void
    {
        config(['app.timezone' => $timezone]);

        date_default_timezone_set($timezone);
    }

    /**
     * Every timezone identifier supported by PHP.
     *
     * @return list<string>
     */
    public static function identifiers(): array
    {
        return once(fn (): array => DateTimeZone::listIdentifiers(DateTimeZone::ALL));
    }

    /**
     * The timezone identifiers grouped by region for the settings selector.
     *
     * @return array<string, list<string>>
     */
    public static function groupedIdentifiers(): array
    {
        $groups = [];

        foreach (self::identifiers() as $timezone) {
            $region = str_contains($timezone, '/')
                ? strstr($timezone, '/', true)
                : $timezone;

            $groups[$region][] = $timezone;
        }

        ksort($groups);

        foreach ($groups as &$timezones) {
            sort($timezones);
        }

        unset($timezones);

        return $groups;
    }

    /**
     * Whether the given value is a supported timezone identifier.
     */
    public static function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, self::identifiers(), true);
    }

    /**
     * The configured application default timezone.
     */
    private static function defaultTimezone(): string
    {
        $timezone = config('app.timezone', 'UTC');

        return is_string($timezone) && $timezone !== '' ? $timezone : 'UTC';
    }
}
