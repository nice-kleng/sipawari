<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;

class ServiceQualityMetrics extends ChartWidget
{
    protected static ?string $heading = 'Metrik Kualitas Layanan';
    protected static ?string $description = 'Breakdown rata-rata per aspek penilaian';
    protected static ?int $sort = 9;

    protected function getData(): array
    {
        $metrics = Rating::approved()
            ->selectRaw('
                AVG(overall_satisfaction) as avg_overall,
                AVG(friendliness) as avg_friendliness,
                AVG(professionalism) as avg_professionalism,
                AVG(service_speed) as avg_speed
            ')
            ->first();

        $data = [
            round($metrics->avg_overall ?? 0, 2),
            round($metrics->avg_friendliness ?? 0, 2),
            round($metrics->avg_professionalism ?? 0, 2),
            round($metrics->avg_speed ?? 0, 2),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Skor',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => [
                'Kepuasan Keseluruhan',
                'Keramahan',
                'Profesionalisme',
                'Kecepatan Layanan',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'r' => [
                    'min' => 0,
                    'max' => 5,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderWidth' => 3,
                ],
            ],
        ];
    }
}
