<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class TrenRatingUnitChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Tren Rating Unit';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = '30';
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari Terakhir',
            '30' => '30 Hari Terakhir',
            '90' => '3 Bulan Terakhir',
            'custom' => 'Custom',
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

        $unitId = $employee->unit_id;

        // Determine date range
        if ($this->filter === 'custom' && $this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::parse($this->startDate);
            $end = \Carbon\Carbon::parse($this->endDate);
        } else {
            $days = (int) $this->filter;
            $start = now()->subDays($days);
            $end = now();
        }

        $data = Trend::query(
            Rating::query()
                ->whereHas('employee', function ($query) use ($unitId) {
                    $query->where('unit_id', $unitId);
                })
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
                    'label' => 'Rata-rata Rating',
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
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user->hasRole('kepala_unit')) {
            return false;
        }

        return $user->employee !== null;
    }
}
