<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BreakdownAspekPenilaianUnitWidget extends ChartWidget
{
    protected static ?string $heading = 'Breakdown Aspek Penilaian Unit';
    protected static ?int $sort = 8;

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

        $averages = Rating::whereHas('employee', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('is_approved', true)
            ->select([
                DB::raw('AVG(overall_satisfaction) as avg_satisfaction'),
                DB::raw('AVG(friendliness) as avg_friendliness'),
                DB::raw('AVG(professionalism) as avg_professionalism'),
                DB::raw('AVG(service_speed) as avg_speed'),
            ])
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Per Aspek',
                    'data' => [
                        round($averages->avg_satisfaction ?? 0, 2),
                        round($averages->avg_friendliness ?? 0, 2),
                        round($averages->avg_professionalism ?? 0, 2),
                        round($averages->avg_speed ?? 0, 2),
                    ],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(245, 158, 11, 0.5)',
                        'rgba(239, 68, 68, 0.5)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Kepuasan Keseluruhan', 'Keramahan', 'Profesionalisme', 'Kecepatan Layanan'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
