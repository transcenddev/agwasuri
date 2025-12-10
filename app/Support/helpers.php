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
