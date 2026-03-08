<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rating;
use App\Models\Unit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UnitStatsWidget extends BaseWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return [];
        }

        // Ambil ID diri sendiri dan seluruh bawahan secara rekursif
        $subordinateIds = $employee->allSubordinateIds();
        $teamIds = array_merge([$employee->id], $subordinateIds);

        $totalEmployees = Employee::whereIn('id', $subordinateIds)
            ->where('is_active', true)
            ->count();

        $ratingsThisMonth = Rating::whereIn('employee_id', $teamIds)
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $avgRating = Rating::whereIn('employee_id', $teamIds)
            ->where('is_approved', true)
            ->avg('overall_satisfaction') ?? 0;

        $lastMonthAvg = Rating::whereIn('employee_id', $teamIds)
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction') ?? 0;

        $trend = $lastMonthAvg > 0
            ? (($avgRating - $lastMonthAvg) / $lastMonthAvg) * 100
            : 0;

        return [
            Stat::make('Total Anggota Tim', $totalEmployees)
                ->description('Karyawan di bawah struktur Anda')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Rating Tim Bulan Ini', $ratingsThisMonth)
                ->description('Total penilaian tim bulan ini')
                ->descriptionIcon('heroicon-o-star')
                ->color('success'),

            Stat::make('Rata-rata Rating Tim', number_format($avgRating, 2))
                ->description($trend >= 0
                    ? 'Naik ' . number_format($trend, 1) . '% dari bulan lalu'
                    : 'Turun ' . number_format(abs($trend), 1) . '% dari bulan lalu')
                ->descriptionIcon($trend >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
        ];
    }

    public static function canView(): bool
    {
        return static::canViewShield()
            && auth()->user()->hasPermissionTo('view_team_dashboard')
            && auth()->user()->employee !== null;
    }
}
