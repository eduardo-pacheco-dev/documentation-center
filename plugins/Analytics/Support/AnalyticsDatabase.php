<?php

namespace Plugins\Analytics\Support;

use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Resolves the database connection used by the analytics plugin.
 *
 * When ANALYTICS_DB_CONNECTION is empty the plugin falls back to the
 * application's default connection (useful for tests and local setups
 * without a dedicated database).
 */
class AnalyticsDatabase
{
    /**
     * The dedicated connection name, or null to use the default connection.
     */
    public static function connection(): ?string
    {
        $name = config('analytics.database.connection');

        return is_string($name) && trim($name) !== '' ? trim($name) : null;
    }

    /**
     * The schema builder of the analytics connection.
     */
    public static function schema(): Builder
    {
        return Schema::connection(static::connection());
    }
}
