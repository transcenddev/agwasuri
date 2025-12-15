<?php

use Illuminate\Support\Arr;

if (! function_exists('classifyWaterParam')) {
    /**
     * Classify a water quality parameter using config/water_quality.php ranges.
     *
     * @param string $param  One of: ph, temperature, salinity, do (or variants)
     * @param float|int $value
     * @return array ['label' => string, 'note' => string, 'min' => ?float, 'max' => ?float]
     */
    function classifyWaterParam(string $param, $value): array
    {
        $map = [
            'ph' => 'ph',
            'pH' => 'ph',
            'temperature' => 'temperature_c',
            'temp' => 'temperature_c',
            'temperature_c' => 'temperature_c',
            'salinity' => 'salinity_ppt',
            'salinity_ppt' => 'salinity_ppt',
            'do' => 'dissolved_oxygen_mg_l',
            'dissolved_oxygen' => 'dissolved_oxygen_mg_l',
            'dissolved_oxygen_mg_l' => 'dissolved_oxygen_mg_l',
        ];

        $key = $map[$param] ?? null;
        if (! $key) {
            // try lowercase key match
            $key = $map[strtolower($param)] ?? null;
        }

        $config = config('water_quality');
        if (! $config || ! $key || ! isset($config[$key]) || ! is_array($config[$key])) {
            return ['label' => 'UNKNOWN', 'note' => 'No classification available', 'min' => null, 'max' => null];
        }

        $ranges = $config[$key];

        // Normalize numeric value
        $num = null;
        if (is_numeric($value)) {
            $num = $value + 0;
        } else {
            // attempt parse
            if (is_string($value)) {
                $v = trim($value);
                if (is_numeric($v)) $num = $v + 0;
            }
        }

        foreach ($ranges as $r) {
            $min = array_key_exists('min', $r) ? $r['min'] : null;
            $max = array_key_exists('max', $r) ? $r['max'] : null;

            $minOk = true;
            $maxOk = true;

            if (! is_null($min) && ! is_null($num)) {
                $minOk = ($num >= $min);
            }
            if (! is_null($max) && ! is_null($num)) {
                $maxOk = ($num <= $max);
            }

            // If value is non-numeric, allow matching ranges that don't rely
            // on numeric comparisons (rare). Otherwise, require numeric comparisons.
            if (! is_null($num)) {
                if ($minOk && $maxOk) {
                    return ['label' => $r['label'] ?? 'UNKNOWN', 'note' => $r['note'] ?? '', 'min' => $min, 'max' => $max];
                }
            }
        }

        return ['label' => 'UNKNOWN', 'note' => 'No matching range', 'min' => null, 'max' => null];
    }
}

if (! function_exists('map_classification_to_status')) {
    /**
     * Map a classification label (e.g. BEST, GOOD, BAD, HARM)
     * to a normalized status key used by UI (`best`, `good`, `bad`, `harm`, `unknown`).
     */
    function map_classification_to_status(string $label): string
    {
        $label = trim(strtoupper($label));

        return match ($label) {
            'BEST' => 'best',
            'GOOD' => 'good',
            'BAD' => 'bad',
            'HARM' => 'harm',
            default => 'unknown',
        };
    }
}

if (! function_exists('status_color')) {
    /**
     * Return a hex color for a status key from config. Falls back to gray.
     * Accepts either the normalized key or a classification label.
     */
    function status_color(string $statusOrLabel): string
    {
        $key = strtolower($statusOrLabel);

        // If a raw label was provided, map it first
        if (! in_array($key, ['best','good','bad','harm','unknown'], true)) {
            $key = map_classification_to_status($statusOrLabel);
        }

        $colors = config('water_quality.status_colors', []);

        return $colors[$key] ?? $colors['unknown'] ?? '#6B7280';
    }
}

if (! function_exists('hex_to_rgba')) {
    /**
     * Convert a hex color string to an rgba(...) string. Accepts 3 or 6 char hex, with or without '#'.
     */
    function hex_to_rgba(string $hex, float $alpha = 1.0): string
    {
        $hex = trim($hex);
        if (str_starts_with($hex, '#')) {
            $hex = substr($hex, 1);
        }

        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat($hex[0], 2));
            $g = hexdec(str_repeat($hex[1], 2));
            $b = hexdec(str_repeat($hex[2], 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        $alpha = max(0, min(1, $alpha));

        return sprintf('rgba(%d,%d,%d,%.3f)', $r, $g, $b, $alpha);
    }
}

if (! function_exists('water_quality_param_keys')) {
    /**
     * Return canonical ordered parameter keys used throughout the app.
     * Order: Temperature, Salinity, Dissolved Oxygen, pH Level
     *
     * @return array<int,string>
     */
    function water_quality_param_keys(): array
    {
        return ['temperature', 'salinity', 'dissolved_oxygen', 'ph_level'];
    }
}

if (! function_exists('water_quality_param_labels')) {
    /**
     * Return a mapping of parameter key -> display label (exact wording required).
     *
     * @return array<string,string>
     */
    function water_quality_param_labels(): array
    {
        return [
            'temperature' => 'Temperature (°C)',
            'salinity' => 'Salinity (ppt)',
            'dissolved_oxygen' => 'Dissolved Oxygen (mg/L)',
            'ph_level' => 'pH Level',
        ];
    }
}

if (! function_exists('water_quality_colors_ordered')) {
    /**
     * Return ordered array of hex colors matching the canonical param keys.
     * Useful for chart color arrays where order matters.
     *
     * @return array<string>
     */
    function water_quality_colors_ordered(): array
    {
        $cfg = config('water_colors.series', []);
        $keys = water_quality_param_keys();
        $out = [];
        foreach ($keys as $k) {
            // map keys used by config: 'temperature','salinity','do','ph'
            if ($k === 'dissolved_oxygen') {
                $color = $cfg['do'] ?? null;
            } else {
                $cfgKey = $k === 'ph_level' ? 'ph' : $k;
                $color = $cfg[$cfgKey] ?? null;
            }
            $out[] = $color ?? '#6B7280';
        }

        return $out;
    }
}
