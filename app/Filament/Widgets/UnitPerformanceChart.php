<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UnitPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Performa Per Unit/Departemen';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $unitPerformance = Unit::select('units.id', 'units.name')
            ->join('employees', 'units.id', '=', 'employees.unit_id')
            ->join('ratings', 'employees.id', '=', 'ratings.employee_id')
            ->where('ratings.is_approved', true)
            ->where('employees.is_active', true)
            ->groupBy('units.id', 'units.name')
            ->selectRaw('AVG(ratings.overall_satisfaction) as avg_rating')
            ->selectRaw('COUNT(ratings.id) as total_ratings')
            ->having('total_ratings', '>', 0)
            ->orderBy('avg_rating', 'desc')
            ->get();

        $labels = [];
        $avgData = [];
        $countData = [];

        foreach ($unitPerformance as $unit) {
            $labels[] = substr($unit->name, 0, 25);
            $avgData[] = round($unit->avg_rating, 2);
            $countData[] = $unit->total_ratings;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Rating',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Jumlah Rating',
                    'data' => $countData,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.6)',
                    'type' => 'line',
                    'yAxisID' => 'y1',
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
                    'min' => 0,
                    'max' => 5,
                    'title' => [
                        'display' => true,
                        'text' => 'Rata-rata Rating',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Rating',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
