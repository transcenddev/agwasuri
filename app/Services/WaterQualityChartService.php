<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WaterQualityChartService
{
    /**
     * Build short labels from rows (time only: H:i)
     *
     * @param Collection $rows
     * @return array
     */
    public function buildLabels(Collection $rows): array
    {
        return $rows->map(fn($r) => Carbon::parse($r->recorded_at)->format('H:i'))->all();
    }

    /**
     * Build numeric series arrays keyed by friendly names
     *
     * @param Collection $rows
     * @return array<string,array>
     */
    public function buildSeries(Collection $rows): array
    {
        $temperatureSeries = $rows->map(fn($r) => $r->temperature !== null ? (float) $r->temperature : null)->all();
        $salinitySeries = $rows->map(fn($r) => $r->salinity !== null ? (float) $r->salinity : null)->all();
        $doSeries = $rows->map(fn($r) => $r->dissolved_oxygen !== null ? (float) $r->dissolved_oxygen : null)->all();
        $phSeries = $rows->map(fn($r) => $r->ph_level !== null ? (float) $r->ph_level : null)->all();

        return [
            'Temperature' => $temperatureSeries,
            'Salinity' => $salinitySeries,
            'Dissolved Oxygen' => $doSeries,
            'pH' => $phSeries,
        ];
    }

    /**
     * Build a conservative serializable line chart array (fallback shape)
     *
     * @param array $labels
     * @param array $series
     * @param array $colors
     * @param array $visible
     * @param int $userId
     * @return array
     */
    public function buildLineChartModelArray(array $labels, array $series, array $colors, array $visible, int $userId): array
    {
        $lineData = [];
        foreach ($series as $seriesName => $values) {
            $points = [];
            foreach ($labels as $idx => $label) {
                $points[] = ['title' => $label, 'value' => $values[$idx] ?? null, 'extras' => new \stdClass()];
            }
            $lineData[$seriesName] = $points;
        }

        $labelCount = count($labels);
        $tickAmount = max(2, min(8, $labelCount));

        return [
            'title' => 'Water Quality Data',
            'data' => $lineData,
            'xAxis' => ['categories' => $labels],
            'colors' => $colors,
            'animated' => false,
            'chart' => [
                // default pixel height (can be overridden by jsonConfig)
                'height' => 700,
            ],
            // client-side options (vendor expects lower-case xaxis)
            'xaxis' => [
                'tickAmount' => $tickAmount,
                'labels' => [
                    'rotate' => -30,
                ],
            ],
            // helper metadata for the Blade/JS
            'meta' => [
                'series_keys' => ['temperature', 'salinity', 'dissolved_oxygen', 'ph_level'],
                'series_names' => array_keys($series),
            ],
            // also include jsonConfig placeholder for wrapper merging
            'jsonConfig' => [
                'chart' => [
                    'height' => 700,
                    'id' => 'water-quality-' . $userId,
                ],
            ],
        ];
    }
}
