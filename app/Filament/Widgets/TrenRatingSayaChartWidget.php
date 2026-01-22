<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TrenRatingSayaChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tren Rating Saya';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari Terakhir',
            '30' => '30 Hari Terakhir',
            '90' => '3 Bulan Terakhir',
            '180' => '6 Bulan Terakhir',
        ];
    }

    protected function getData(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $days = (int) $this->filter;
        $start = now()->subDays($days);
        $end = now();

        $data = Trend::query(
            Rating::query()
                ->where('employee_id', $employee->id)
                ->where('is_approved', true)
        )
            ->between(
                start: $start,
                end: $end,
            )
            ->perDay()
            ->average('overall_satisfaction');

        return [
            'datasets' => [
                [
                    'label' => 'Rating Keseluruhan',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 5,
                    'ticks' => [
                        'stepSize' => 0.5,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user->hasRole('karyawan')) {
            return false;
        }

        return $user->employee !== null;
    }
}
