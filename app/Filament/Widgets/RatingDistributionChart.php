<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;

class RatingDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Rating';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $distribution = [
            5 => Rating::approved()->where('overall_satisfaction', 5)->count(),
            4 => Rating::approved()->where('overall_satisfaction', 4)->count(),
            3 => Rating::approved()->where('overall_satisfaction', 3)->count(),
            2 => Rating::approved()->where('overall_satisfaction', 2)->count(),
            1 => Rating::approved()->where('overall_satisfaction', 1)->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Rating',
                    'data' => array_values($distribution),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // 5 stars - Green
                        'rgba(59, 130, 246, 0.8)',   // 4 stars - Blue
                        'rgba(251, 191, 36, 0.8)',   // 3 stars - Yellow
                        'rgba(249, 115, 22, 0.8)',   // 2 stars - Orange
                        'rgba(239, 68, 68, 0.8)',    // 1 star - Red
                    ],
                ],
            ],
            'labels' => [
                '⭐⭐⭐⭐⭐ Excellent (5)',
                '⭐⭐⭐⭐ Good (4)',
                '⭐⭐⭐ Average (3)',
                '⭐⭐ Poor (2)',
                '⭐ Very Poor (1)',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
