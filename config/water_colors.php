<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Water Quality Series Colors
    |--------------------------------------------------------------------------
    |
    | Canonical hex colors for the four monitored parameters, keyed by the names
    | used across the charting helpers and views:
    |   temperature, salinity, do (dissolved oxygen), ph
    |
    | Consumers: WaterQualityChart, WaterQualityCard (sparklines),
    | app/Support/helpers.php (water_quality_colors_ordered) and the CSS variables
    | exposed in resources/views/layouts/app.blade.php.
    |
    | This is the single source of truth; callers keep the same values as their
    | own fallbacks if a key is missing.
    |
    */

    'series' => [
        'temperature' => '#EF4444',
        'salinity' => '#14B8A6',
        'do' => '#2563EB',
        'ph' => '#8B5CF6',
    ],
];
