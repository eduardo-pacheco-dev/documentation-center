<?php

namespace Plugins\Stations\Support;

class CoordinateParser
{
    /**
     * Convert a coordinate typed as decimal degrees or degrees/minutes/seconds to decimal degrees.
     *
     * Accepted examples: "-23.665278", "-23,665278", "23°39'55\"S", "23 39 55 S", "46°38'28\"W".
     */
    public static function toDecimal(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $normalized = str_replace(
            ['º', '°', '˚', '′', '’', '″', '”'],
            ' ',
            strtoupper($value)
        );

        preg_match_all('/-?\d+(?:[.,]\d+)?/', $normalized, $matches);

        $parts = array_map(
            static fn (string $part): float => (float) str_replace(',', '.', $part),
            $matches[0]
        );

        if ($parts === [] || count($parts) > 3) {
            return null;
        }

        $degrees = abs($parts[0]);
        $minutes = $parts[1] ?? 0.0;
        $seconds = $parts[2] ?? 0.0;

        if ($minutes >= 60 || $seconds >= 60) {
            return null;
        }

        $negative = str_starts_with(ltrim($normalized), '-')
            || preg_match('/[SW]/', $normalized) === 1;

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        return round($negative ? -$decimal : $decimal, 6);
    }
}
