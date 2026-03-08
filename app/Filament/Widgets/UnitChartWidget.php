<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class UnitChartWidget extends ChartWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?string $heading = 'Grafik Unit';
    protected static ?int $sort = 12;

    public ?string $filter = 'trend';

    protected function getFilters(): ?array
    {
        return [
            'trend'     => 'Tren Rating (30 Hari)',
            'trend_90'  => 'Tren Rating (3 Bulan)',
            'breakdown' => 'Breakdown Aspek Penilaian',
            'compare'   => 'Komparasi Antar Unit',
        ];
    }

    protected function getData(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return ['datasets' => [], 'labels' => []];
        }

        $subordinateIds = $employee->allSubordinateIds();
        $teamIds = array_merge([$employee->id], $subordinateIds);

        return match (true) {
            str_starts_with($this->filter, 'trend') => $this->getTrendData($teamIds),
            $this->filter === 'breakdown'            => $this->getBreakdownData($teamIds),
            $this->filter === 'compare'              => $this->getCompareData($teamIds),
            default                                  => $this->getTrendData($teamIds),
        };
    }

    protected function getType(): string
    {
        return match ($this->filter) {
            'breakdown' => 'bar',
            'compare'   => 'bar',
            default     => 'line',
        };
    }

    protected function getOptions(): array
    {
        return match ($this->filter) {
            'breakdown' => [
                'scales' => ['y' => ['beginAtZero' => true, 'max' => 5]],
            ],
            'compare' => [
                'indexAxis' => 'y',
                'scales'    => ['x' => ['min' => 0, 'max' => 5]],
            ],
            default => [
                'scales' => [
                    'y' => ['beginAtZero' => true, 'max' => 5, 'ticks' => ['stepSize' => 0.5]],
                ],
            ],
        };
    }

    private function getTrendData(array $teamIds): array
    {
        $days  = $this->filter === 'trend_90' ? 90 : 30;
        $start = now()->subDays($days);
        $end   = now();

        $data = Trend::query(
            Rating::query()
                ->whereIn('employee_id', $teamIds)
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

    private function getBreakdownData(array $teamIds): array
    {
        $averages = Rating::whereIn('employee_id', $teamIds)
            ->where('is_approved', true)
            ->select([
                DB::raw('AVG(overall_satisfaction) as avg_satisfaction'),
                DB::raw('AVG(friendliness) as avg_friendliness'),
                DB::raw('AVG(professionalism) as avg_professionalism'),
                DB::raw('AVG(service_speed) as avg_speed'),
            ])
            ->first();

        return [
            'datasets' => [[
                'label' => 'Rata-rata Per Aspek',
                'data'  => [
                    round($averages->avg_satisfaction ?? 0, 2),
                    round($averages->avg_friendliness ?? 0, 2),
                    round($averages->avg_professionalism ?? 0, 2),
                    round($averages->avg_speed ?? 0, 2),
                ],
                'backgroundColor' => [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                ],
                'borderColor' => [
                    'rgb(59, 130, 246)', 'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)', 'rgb(239, 68, 68)',
                ],
                'borderWidth' => 1,
            ]],
            'labels' => ['Kepuasan Keseluruhan', 'Keramahan', 'Profesionalisme', 'Kecepatan Layanan'],
        ];
    }

    private function getCompareData(array $teamIds): array
    {
        // Perbandingan rata-rata anggota tim
        $teamMembers = \App\Models\Employee::whereIn('id', $teamIds)
            ->where('is_active', true)
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($teamMembers as $member) {
            $labels[] = \Illuminate\Support\Str::limit($member->name, 15);
            $avg = Rating::where('employee_id', $member->id)
                ->where('is_approved', true)
                ->avg('overall_satisfaction') ?? 0;
            
            $data[] = round($avg, 2);
            $colors[] = $member->id === auth()->user()->employee->id
                ? 'rgba(34, 197, 94, 0.85)'
                : 'rgba(59, 130, 246, 0.6)';
        }

        return [
            'datasets' => [['label' => 'Rata-rata Rating', 'data' => $data, 'backgroundColor' => $colors]],
            'labels'   => $labels,
        ];
    }

    public static function canView(): bool
    {
        return static::canViewShield()
            && auth()->user()->hasPermissionTo('view_team_dashboard')
            && auth()->user()->employee !== null;
    }
}
