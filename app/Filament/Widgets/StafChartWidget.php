<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StafChartWidget extends ChartWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?string $heading = 'Tren Rating Saya';
    protected static ?int $sort = 21;
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7'   => '7 Hari Terakhir',
            '30'  => '30 Hari Terakhir',
            '90'  => '3 Bulan Terakhir',
            '180' => '6 Bulan Terakhir',
        ];
    }

    protected function getData(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return ['datasets' => [], 'labels' => []];
        }

        $days  = (int) $this->filter;
        $start = now()->subDays($days);
        $end   = now();

        $data = Trend::query(
            Rating::query()
                ->where('employee_id', $employee->id)
                ->where('is_approved', true)
        )
            ->between(start: $start, end: $end)
            ->perDay()
            ->average('overall_satisfaction');

        return [
            'datasets' => [[
                'label'           => 'Rata-rata Rating Harian',
                'data'            => $data->map(fn(TrendValue $v) => $v->aggregate),
                'borderColor'     => 'rgb(59, 130, 246)',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'fill'            => true,
            ]],
            'labels' => $data->map(fn(TrendValue $v) => $v->date),
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
                    'max'         => 5,
                    'ticks'       => ['stepSize' => 0.5],
                ],
            ],
            'plugins' => ['legend' => ['display' => true]],
        ];
    }

    public static function canView(): bool
    {
        return static::canViewShield()
            && auth()->user()->employee !== null
            && !auth()->user()->hasPermissionTo('view_team_dashboard')
            && !auth()->user()->hasPermissionTo('view_global_dashboard');
    }
}
