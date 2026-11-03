<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Periods
    |--------------------------------------------------------------------------
    |
    | Preset periods (in days) offered in the dashboard filter. The first
    | entry is the default.
    |
    */

    'periods' => [30, 7, 90, 365],

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    |
    | Maximum number of days a custom period can cover and how many rows the
    | ranking tables display.
    |
    */

    'max_period_days' => 366,
    'ranking_limit' => 6,

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Optional dedicated connection for the analytics tables. Leave the
    | connection empty to store the data on the application database.
    |
    */

    'database' => [
        'connection' => env('ANALYTICS_DB_CONNECTION'),
        'host' => env('ANALYTICS_DB_HOST', '127.0.0.1'),
        'port' => env('ANALYTICS_DB_PORT', '5432'),
        'database' => env('ANALYTICS_DB_DATABASE', 'documentation_analytics'),
        'username' => env('ANALYTICS_DB_USERNAME'),
        'password' => env('ANALYTICS_DB_PASSWORD'),
        'search_path' => env('ANALYTICS_DB_SCHEMA', 'public'),
        'sslmode' => env('ANALYTICS_DB_SSLMODE', 'prefer'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Imports
    |--------------------------------------------------------------------------
    |
    | Large spreadsheets need much more memory than PHP's default limit.
    | The import job raises the worker memory limit to this value before
    | reading the file. Use "-1" to leave it unlimited.
    |
    */

    'import_memory_limit' => env('ANALYTICS_IMPORT_MEMORY_LIMIT', '1G'),

    /*
    |--------------------------------------------------------------------------
    | Snapshots
    |--------------------------------------------------------------------------
    |
    | Number of latest snapshots shown in the history card.
    |
    */

    'snapshots_to_show' => 10,

];
