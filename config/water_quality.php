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
        ['min' => null,  'max' => 5.0, 'label' => 'HARMFUL', 'note' => 'highly acidic'],
        ['min' => 5.0,   'max' => 6.4, 'label' => 'HARMFUL', 'note' => 'acidic / suboptimal'],
        ['min' => 6.5,   'max' => 8.5, 'label' => 'GOOD',    'note' => 'optimal (DENR standard)'],
        ['min' => 6.5,   'max' => 9.0, 'label' => 'GOOD',    'note' => 'acceptable range for production'],
        ['min' => 7.9,   'max' => 8.3, 'label' => 'GOOD',    'note' => 'typical Manila Bay range'],
        ['min' => 9.0,   'max' => null,'label' => 'HARMFUL', 'note' => 'highly alkaline'],
    ],

    'temperature_c' => [
        ['min' => null,  'max' => 20.0, 'label' => 'HARMFUL', 'note' => 'sub-lethal / harmful to growth'],
        ['min' => 20.0,  'max' => 25.0, 'label' => 'BAD',     'note' => 'suboptimal for culture'],
        ['min' => 25.0,  'max' => 31.0, 'label' => 'GOOD',    'note' => 'optimal (DENR recommended)'],
        ['min' => 26.7,  'max' => 31.0, 'label' => 'GOOD',    'note' => 'typical observed range in Philippine studies'],
        ['min' => 31.0,  'max' => 35.0, 'label' => 'GOOD',    'note' => 'upper tolerance / acceptable'],
        ['min' => 35.0,  'max' => null, 'label' => 'HARMFUL', 'note' => 'lethal / fatal to many species'],
    ],

    'salinity_ppt' => [
        ['min' => null, 'max' => 7.0,  'label' => 'Low',      'note' => 'freshwater–brackish conditions'],
        ['min' => 7.0,  'max' => 25.0, 'label' => 'Moderate', 'note' => 'low–moderate brackish'],
        ['min' => 25.0, 'max' => null, 'label' => 'High',     'note' => 'high salinity / limited freshwater species'],
    ],

    'dissolved_oxygen_mg_l' => [
        ['min' => null, 'max' => 2.0,  'label' => 'HARMFUL', 'note' => 'critical / lethal levels'],
        ['min' => 2.0,  'max' => 3.0,  'label' => 'HARMFUL', 'note' => 'severe stress'],
        ['min' => 3.0,  'max' => 5.0,  'label' => 'GOOD',    'note' => 'suboptimal but survivable'],
        ['min' => 5.0,  'max' => null, 'label' => 'GOOD',    'note' => 'optimal (commonly recommended ≥5.0 mg/L)'],
        ['min' => 3.8,  'max' => 7.6,  'label' => 'GOOD',    'note' => 'typical observed range in local ponds'],
        ['min' => 13.0, 'max' => 14.0, 'label' => 'GOOD',    'note' => 'very high DO in some case studies'],
    ],

    'meta' => [
        'source' => 'docs/WATER_QUALITY_SCALES.md',
        'notes' => 'Ranges derived from project documentation and DENR guidance; overlapping entries capture both regulatory optimal ranges and observed typical ranges.',
    ],
    // Handy numeric thresholds used for chart annotations and quick reference
    'thresholds' => [
        'dissolved_oxygen_mg_l' => 5.0,
        'temperature_c' => [25.0, 31.0],
        'ph' => [6.5, 8.5],
        'salinity_ppt' => [7.0, 25.0],
    ],
];
