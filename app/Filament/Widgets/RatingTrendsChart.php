<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RatingTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Rating 6 Bulan Terakhir';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $count = Rating::approved()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $avgRating = Rating::approved()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->avg('overall_satisfaction') ?? 0;

            $data['ratings'][] = $count;
            $data['average'][] = round($avgRating, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Rating',
                    'data' => $data['ratings'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Rata-rata Kepuasan',
                    'data' => $data['average'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Rating',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Rata-rata (1-5)',
                    ],
                    'min' => 0,
                    'max' => 5,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
