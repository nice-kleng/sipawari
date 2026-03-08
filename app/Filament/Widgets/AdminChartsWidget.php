<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rater;
use App\Models\Rating;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AdminChartsWidget extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Analitik & Grafik';
    protected static ?int $sort = 2;

    public ?string $filter = 'trend';

    protected function getFilters(): ?array
    {
        return [
            'trend'        => 'Tren Rating 6 Bulan',
            'top'          => 'Top 10 Karyawan Terbaik',
            'distribution' => 'Distribusi Rating',
            'unit'         => 'Performa Per Unit',
            'quality'      => 'Metrik Kualitas Layanan',
            'demographics' => 'Demografi Penilai',
        ];
    }

    protected function getData(): array
    {
        return match ($this->filter) {
            'top'          => $this->getTopPerformersData(),
            'distribution' => $this->getDistributionData(),
            'unit'         => $this->getUnitPerformanceData(),
            'quality'      => $this->getQualityMetricsData(),
            'demographics' => $this->getDemographicsData(),
            default        => $this->getTrendData(),
        };
    }

    protected function getType(): string
    {
        return match ($this->filter) {
            'top'          => 'bar',
            'distribution' => 'doughnut',
            'unit'         => 'bar',
            'quality'      => 'radar',
            'demographics' => 'pie',
            default        => 'line',
        };
    }

    protected function getOptions(): array
    {
        return match ($this->filter) {
            'trend' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales'  => [
                    'y'  => [
                        'type' => 'linear', 'display' => true, 'position' => 'left',
                        'title' => ['display' => true, 'text' => 'Jumlah Rating'],
                    ],
                    'y1' => [
                        'type' => 'linear', 'display' => true, 'position' => 'right',
                        'title' => ['display' => true, 'text' => 'Rata-rata (1-5)'],
                        'min' => 0, 'max' => 5,
                        'grid' => ['drawOnChartArea' => false],
                    ],
                ],
            ],
            'top' => [
                'indexAxis' => 'y',
                'plugins'   => ['legend' => ['display' => false]],
                'scales'    => [
                    'x' => ['min' => 0, 'max' => 5, 'title' => ['display' => true, 'text' => 'Rating (1-5)']],
                ],
            ],
            'unit' => [
                'plugins' => ['legend' => ['display' => true]],
                'scales'  => [
                    'y'  => ['type' => 'linear', 'display' => true, 'position' => 'left', 'min' => 0, 'max' => 5,
                        'title' => ['display' => true, 'text' => 'Rata-rata Rating']],
                    'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right',
                        'title' => ['display' => true, 'text' => 'Jumlah Rating'],
                        'grid' => ['drawOnChartArea' => false]],
                ],
            ],
            'quality' => [
                'scales'   => ['r' => ['min' => 0, 'max' => 5, 'ticks' => ['stepSize' => 1]]],
                'elements' => ['line' => ['borderWidth' => 3]],
            ],
            'distribution' => [
                'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
            ],
            default => [],
        };
    }

    // ── Data Methods ───────────────────────────────────────────────

    private function getTrendData(): array
    {
        $labels  = [];
        $counts  = [];
        $avgs    = [];

        for ($i = 5; $i >= 0; $i--) {
            $date     = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $counts[] = Rating::approved()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $avgs[]   = round(
                Rating::approved()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->avg('overall_satisfaction') ?? 0,
                2
            );
        }

        return [
            'datasets' => [
                ['label' => 'Jumlah Rating', 'data' => $counts,
                    'borderColor' => 'rgb(59, 130, 246)', 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'yAxisID' => 'y'],
                ['label' => 'Rata-rata Kepuasan', 'data' => $avgs,
                    'borderColor' => 'rgb(34, 197, 94)', 'backgroundColor' => 'rgba(34, 197, 94, 0.1)', 'yAxisID' => 'y1'],
            ],
            'labels' => $labels,
        ];
    }

    private function getTopPerformersData(): array
    {
        $employees = Employee::where('is_active', true)
            ->withCount(['ratings as approved_ratings_count' => fn($q) => $q->where('is_approved', true)])
            ->withAvg(['ratings as avg_rating' => fn($q) => $q->where('is_approved', true)], 'overall_satisfaction')
            ->having('approved_ratings_count', '>', 0)
            ->orderBy('avg_rating', 'desc')
            ->take(10)
            ->get();

        $labels = [];
        $data   = [];
        $colors = [];

        foreach ($employees as $emp) {
            $labels[] = substr($emp->name, 0, 20) . ($emp->unit ? ' - ' . substr($emp->unit->name, 0, 15) : '');
            $rating   = round($emp->avg_rating ?? 0, 2);
            $data[]   = $rating;
            $colors[] = $rating >= 4.5 ? 'rgba(34, 197, 94, 0.8)'
                : ($rating >= 4.0 ? 'rgba(59, 130, 246, 0.8)'
                    : ($rating >= 3.5 ? 'rgba(251, 191, 36, 0.8)'
                        : 'rgba(239, 68, 68, 0.8)'));
        }

        return [
            'datasets' => [['label' => 'Rata-rata Rating', 'data' => $data, 'backgroundColor' => $colors]],
            'labels'   => $labels,
        ];
    }

    private function getDistributionData(): array
    {
        $dist = [];
        for ($i = 5; $i >= 1; $i--) {
            $dist[$i] = Rating::approved()->where('overall_satisfaction', $i)->count();
        }

        return [
            'datasets' => [[
                'label' => 'Jumlah Rating',
                'data'  => array_values($dist),
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
            ]],
            'labels' => ['⭐⭐⭐⭐⭐ Excellent (5)', '⭐⭐⭐⭐ Good (4)', '⭐⭐⭐ Average (3)', '⭐⭐ Poor (2)', '⭐ Very Poor (1)'],
        ];
    }

    private function getUnitPerformanceData(): array
    {
        $units = Unit::select('units.id', 'units.name')
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

        $labels   = [];
        $avgData  = [];
        $cntData  = [];

        foreach ($units as $unit) {
            $labels[]  = substr($unit->name, 0, 25);
            $avgData[] = round($unit->avg_rating, 2);
            $cntData[] = $unit->total_ratings;
        }

        return [
            'datasets' => [
                ['label' => 'Rata-rata Rating', 'data' => $avgData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)', 'yAxisID' => 'y'],
                ['label' => 'Jumlah Rating', 'data' => $cntData,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.6)', 'type' => 'line', 'yAxisID' => 'y1'],
            ],
            'labels' => $labels,
        ];
    }

    private function getQualityMetricsData(): array
    {
        $m = Rating::approved()
            ->selectRaw('AVG(overall_satisfaction) as avg_overall, AVG(friendliness) as avg_friendliness,
                         AVG(professionalism) as avg_professionalism, AVG(service_speed) as avg_speed')
            ->first();

        return [
            'datasets' => [[
                'label'                  => 'Rata-rata Skor',
                'data'                   => [
                    round($m->avg_overall ?? 0, 2),
                    round($m->avg_friendliness ?? 0, 2),
                    round($m->avg_professionalism ?? 0, 2),
                    round($m->avg_speed ?? 0, 2),
                ],
                'backgroundColor'        => 'rgba(59, 130, 246, 0.2)',
                'borderColor'            => 'rgb(59, 130, 246)',
                'pointBackgroundColor'   => 'rgb(59, 130, 246)',
                'pointBorderColor'       => '#fff',
                'pointHoverBackgroundColor' => '#fff',
                'pointHoverBorderColor'  => 'rgb(59, 130, 246)',
            ]],
            'labels' => ['Kepuasan Keseluruhan', 'Keramahan', 'Profesionalisme', 'Kecepatan Layanan'],
        ];
    }

    private function getDemographicsData(): array
    {
        $genderData = Rater::select('gender', DB::raw('count(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();

        $labels = [];
        $data   = [];
        $colors = [];

        foreach ($genderData as $item) {
            $labels[] = match ($item->gender) {
                'male'   => 'Laki-laki',
                'female' => 'Perempuan',
                default  => 'Lainnya',
            };
            $data[]   = $item->count;
            $colors[] = $item->gender === 'male'
                ? 'rgba(59, 130, 246, 0.8)'
                : 'rgba(236, 72, 153, 0.8)';
        }

        return [
            'datasets' => [['data' => $data, 'backgroundColor' => $colors]],
            'labels'   => $labels,
        ];
    }
}
