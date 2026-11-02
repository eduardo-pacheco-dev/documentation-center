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
    | Snapshots
    |--------------------------------------------------------------------------
    |
    | Number of latest snapshots shown in the history card.
    |
    */

    'snapshots_to_show' => 10,

];
