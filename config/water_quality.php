<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Water Quality Classification Ranges
    |--------------------------------------------------------------------------
    |
    | Each entry is an array with keys: `min`, `max`, `label`, `note`.
    | `min` or `max` may be null to indicate an open-ended range.
    |
    */

    'ph' => [
        ['min' => null, 'max' => 6.50, 'label' => 'CRITICAL', 'note' => 'Below 6.5 - outside acceptable range (DENR AO 2016-08 Class C: 6.5-8.5; Class D Critical: 6.0-9.0)'],
        ['min' => 6.50, 'max' => 9.00, 'label' => 'GOOD', 'note' => 'Within acceptable range for freshwater species (DENR AO 2016-08; PHILMINAQ: 6.5-9.0)'],
        ['min' => 9.00, 'max' => null, 'label' => 'CRITICAL', 'note' => 'Above 9.0 - outside acceptable range (DENR AO 2016-08 Class D Critical maximum)'],
    ],

    'temperature_c' => [
        ['min' => null, 'max' => 26.00, 'label' => 'CRITICAL', 'note' => 'Below 26°C - suboptimal temperature conditions'],
        ['min' => 26.00, 'max' => 32.00, 'label' => 'GOOD', 'note' => 'Optimal range (DENR AO 2016-08: max rise 3°C; PHILMINAQ: below 32°C maintains good oxygen capacity)'],
        ['min' => 32.00, 'max' => null, 'label' => 'CRITICAL', 'note' => 'Above 32°C - significantly reduces oxygen-carrying capacity of water (PHILMINAQ)'],
    ],

    'salinity_ppt' => [
        ['min' => null, 'max' => 0.50, 'label' => 'CRITICAL', 'note' => 'Below 0.5 ppt - below freshwater threshold (DENR AO 2016-08: freshwater <500 ppm or 0.5 ppt)'],
        ['min' => 0.50, 'max' => 30.00, 'label' => 'GOOD', 'note' => 'Acceptable range covering freshwater to brackish water (DENR AO 2016-08; PHILMINAQ)'],
        ['min' => 30.00, 'max' => null, 'label' => 'CRITICAL', 'note' => 'Above 30 ppt - marine water level, may be unsuitable for most cultured species (DENR AO 2016-08: marine ≥30 ppt)'],
    ],

    'dissolved_oxygen_mg_l' => [
        ['min' => null, 'max' => 5.00, 'label' => 'CRITICAL', 'note' => 'Below 5.0 mg/L - insufficient for good growth (DENR AO 2016-08 Class C: min 5 mg/L; PHILMINAQ: at least 5 ppm required)'],
        ['min' => 5.00, 'max' => null, 'label' => 'GOOD', 'note' => '5.0 mg/L and above - adequate for good growth and high production yield (DENR AO 2016-08; PHILMINAQ)'],
    ],

    'meta' => [
        'source' => 'notes/reference/WATER_QUALITY_SCALES.md',
        'notes' => 'Ranges derived from project documentation and DENR guidance; overlapping entries capture both regulatory optimal ranges and observed typical ranges.',
    ],
    // Handy numeric thresholds used for chart annotations and quick reference
    'thresholds' => [
        'dissolved_oxygen_mg_l' => 5.0,
        'temperature_c' => [26.0, 32.0],
        'ph' => [6.5, 9.0],
        'salinity_ppt' => [0.5, 30.0],
    ],
    // UI status -> CSS class mapping for badges, values and row borders
    'status_map' => [
        'GOOD' => [
            'badge' => 'text-green-700 bg-green-50',
            'value' => 'text-green-700',
            'border' => 'border-l-4 border-green-100',
        ],
        'CRITICAL' => [
            'badge' => 'text-red-700 bg-red-50',
            'value' => 'text-red-700 font-semibold',
            'border' => 'border-l-4 border-red-200',
        ],
    ],
];
