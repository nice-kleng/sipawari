<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;

class TopPerformersChart extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Karyawan Terbaik';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $topEmployees = Employee::where('is_active', true)
            ->withCount(['ratings as approved_ratings_count' => function ($query) {
                $query->where('is_approved', true);
            }])
            ->withAvg(['ratings as avg_rating' => function ($query) {
                $query->where('is_approved', true);
            }], 'overall_satisfaction')
            ->having('approved_ratings_count', '>', 0)
            ->orderBy('avg_rating', 'desc')
            ->take(10)
            ->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($topEmployees as $employee) {
            $labels[] = substr($employee->name, 0, 20) . ($employee->unit ? ' - ' . substr($employee->unit->name, 0, 15) : '');
            $data[] = round($employee->avg_rating ?? 0, 2);

            // Color based on rating
            $rating = $employee->avg_rating ?? 0;
            if ($rating >= 4.5) {
                $backgroundColors[] = 'rgba(34, 197, 94, 0.8)'; // Green
            } elseif ($rating >= 4.0) {
                $backgroundColors[] = 'rgba(59, 130, 246, 0.8)'; // Blue
            } elseif ($rating >= 3.5) {
                $backgroundColors[] = 'rgba(251, 191, 36, 0.8)'; // Yellow
            } else {
                $backgroundColors[] = 'rgba(239, 68, 68, 0.8)'; // Red
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Rating',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'min' => 0,
                    'max' => 5,
                    'title' => [
                        'display' => true,
                        'text' => 'Rating (1-5)',
                    ],
                ],
            ],
        ];
    }
}
