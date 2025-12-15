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
        ['min' => null, 'max' => 6.00, 'label' => 'HARM', 'note' => 'extremely outside the acceptable range and may pose high risk to fish health and survival'],
        ['min' => 6.00, 'max' => 6.49, 'label' => 'BAD',  'note' => 'outside the acceptable range; indicates undesirable conditions requiring corrective action'],
        ['min' => 6.50, 'max' => 7.88, 'label' => 'GOOD', 'note' => 'within the acceptable range but outside the typical operating range observed in the study'],
        ['min' => 7.89, 'max' => 8.31, 'label' => 'BEST', 'note' => 'typical operating range observed and within the acceptable standard'],
        ['min' => 8.32, 'max' => 8.50, 'label' => 'GOOD', 'note' => 'within the acceptable range but outside the typical operating range observed in the study'],
        ['min' => 8.51, 'max' => 9.00, 'label' => 'BAD',  'note' => 'outside the acceptable range; indicates undesirable conditions requiring corrective action'],
        ['min' => 9.00, 'max' => null, 'label' => 'HARM', 'note' => 'extremely outside the acceptable range and may pose high risk to fish health and survival'],
    ],

    'temperature_c' => [
        ['min' => null,  'max' => 20.00, 'label' => 'HARM', 'note' => 'sub-lethal or lethal range that may impair survival, consistent with thresholds cited in the study'],
        ['min' => 20.00, 'max' => 24.99, 'label' => 'BAD',  'note' => 'outside the recommended range and may reduce fish performance and stability'],
        ['min' => 25.00, 'max' => 29.83, 'label' => 'GOOD', 'note' => 'within the recommended range but below the typical upper operating range observed in the study'],
        ['min' => 29.84, 'max' => 31.00, 'label' => 'BEST', 'note' => 'upper operating range aligned with observed values while remaining within the recommended limit'],
        ['min' => 31.01, 'max' => 35.00, 'label' => 'BAD',  'note' => 'outside the recommended range and may reduce fish performance and stability'],
        ['min' => 35.00, 'max' => null,  'label' => 'HARM', 'note' => 'sub-lethal or lethal range that may impair survival, consistent with thresholds cited in the study'],
    ],

    'salinity_ppt' => [
        ['min' => null,  'max' => 5.00,  'label' => 'HARM', 'note' => 'very low salinity condition that may increase stress risk and reduce survival for cultured species requiring brackish conditions'],
        ['min' => 5.00,  'max' => 6.99,  'label' => 'BAD',  'note' => 'low range that may be unsuitable for stable culture conditions without careful management'],
        ['min' => 7.00,  'max' => 9.99,  'label' => 'GOOD', 'note' => 'light range that supports more stable conditions than lower ranges'],
        ['min' => 10.00, 'max' => 17.33, 'label' => 'BEST', 'note' => 'optimal operating range aligned with the observed peak range reported in the study'],
        ['min' => 17.34, 'max' => 25.00, 'label' => 'GOOD', 'note' => 'acceptable upper range; requires monitoring to prevent spikes due to evaporation and concentration'],
        ['min' => 25.01, 'max' => null,  'label' => 'HARM', 'note' => 'excessively high salinity that may increase stress risk and reduce survival if sustained'],
    ],

    'dissolved_oxygen_mg_l' => [
        ['min' => null, 'max' => 3.75, 'label' => 'HARM', 'note' => 'below the minimum observed range; indicates critically low oxygen conditions requiring immediate action'],
        ['min' => 3.75, 'max' => 4.99, 'label' => 'BAD',  'note' => 'below the recommended minimum; may result in stress and reduced growth performance'],
        ['min' => 5.00, 'max' => 6.12, 'label' => 'GOOD', 'note' => 'meets the recommended minimum and remains within the observed range'],
        ['min' => 6.13, 'max' => null, 'label' => 'BEST', 'note' => 'satisfies the minimum guideline for fish production'],
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
