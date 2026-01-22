<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsUnitWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return [];
        }

        $unitId = $employee->unit_id;

        // Total karyawan di unit
        $totalEmployees = Employee::where('unit_id', $unitId)
            ->where('is_active', true)
            ->count();

        // Total rating bulan ini
        $totalRatingsThisMonth = Rating::whereHas('employee', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Average rating unit
        $avgRating = Rating::whereHas('employee', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('is_approved', true)
            ->avg('overall_satisfaction');

        // Rating bulan lalu untuk comparison
        $lastMonthAvg = Rating::whereHas('employee', function ($query) use ($unitId) {
            $query->where('unit_id', $unitId);
        })
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction');

        $trend = $lastMonthAvg > 0
            ? (($avgRating - $lastMonthAvg) / $lastMonthAvg) * 100
            : 0;

        return [
            Stat::make('Total Karyawan', $totalEmployees)
                ->description('Karyawan aktif di unit')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Rating Bulan Ini', $totalRatingsThisMonth)
                ->description('Total penilaian bulan ini')
                ->descriptionIcon('heroicon-o-star')
                ->color('success'),

            Stat::make('Rata-rata Rating Unit', number_format($avgRating, 2))
                ->description($trend >= 0 ? "Naik {$trend}%" : "Turun " . abs($trend) . "%")
                ->descriptionIcon($trend >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
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
