<?php

namespace Plugins\Analytics\Support;

use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Stores the live progress of a running import.
 *
 * The progress lives in the cache instead of the analytics table because the
 * importers run inside a database transaction: rows written on that
 * transaction are not visible to the status endpoint until it commits.
 */
class AnalyticsImportProgress
{
    /**
     * How long a progress entry is kept, in seconds.
     */
    private const TTL = 3600;

    /**
     * Store the progress of the given import.
     *
     * @param  array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}  $progress
     */
    public static function put(int|string $importId, array $progress): void
    {
        try {
            Cache::put(self::key($importId), $progress, self::TTL);
        } catch (Throwable) {
            // Progress is best-effort and must never break an import.
        }
    }

    /**
     * Read the progress of the given import, when available.
     *
     * @return array{processed: int, total: int, created: int, updated: int, unchanged: int, skipped: int}|null
     */
    public static function get(int|string $importId): ?array
    {
        try {
            $progress = Cache::get(self::key($importId));
        } catch (Throwable) {
            return null;
        }

        return is_array($progress) ? $progress : null;
    }

    /**
     * Forget the progress of the given import.
     */
    public static function forget(int|string $importId): void
    {
        try {
            Cache::forget(self::key($importId));
        } catch (Throwable) {
            // Progress is best-effort and must never break an import.
        }
    }

    private static function key(int|string $importId): string
    {
        return "analytics:import-progress:{$importId}";
    }
}
