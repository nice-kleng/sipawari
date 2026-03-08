<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class StafStatsWidget extends BaseWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return [];
        }

        $totalRatings = $employee->ratings()
            ->where('is_approved', true)->count();

        $avgRating = $employee->ratings()
            ->where('is_approved', true)->avg('overall_satisfaction') ?? 0;

        $ratingsThisMonth = $employee->ratings()
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthAvg = $employee->ratings()
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction') ?? 0;

        $trend = $lastMonthAvg > 0
            ? (($avgRating - $lastMonthAvg) / $lastMonthAvg) * 100
            : 0;

        $highestRating = $employee->ratings()
            ->where('is_approved', true)->max('overall_satisfaction') ?? 0;

        return [
            Stat::make('Total Rating Diterima', $totalRatings)
                ->description('Rating yang telah disetujui')
                ->descriptionIcon('heroicon-o-star')
                ->color('primary'),

            Stat::make('Rata-rata Rating', number_format($avgRating, 2))
                ->description($trend >= 0
                    ? 'Naik ' . number_format($trend, 1) . '% dari bulan lalu'
                    : 'Turun ' . number_format(abs($trend), 1) . '% dari bulan lalu')
                ->descriptionIcon($trend >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make('Rating Bulan Ini', $ratingsThisMonth)
                ->description('Penilaian di bulan ini')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Rating Tertinggi', number_format($highestRating, 1))
                ->description('Pencapaian terbaik Anda')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        // Tampil untuk semua user yang punya employee tapi BUKAN manager/super admin
        return static::canViewShield()
            && auth()->user()->employee !== null
            && !auth()->user()->hasPermissionTo('view_team_dashboard')
            && !auth()->user()->hasPermissionTo('view_global_dashboard');
    }
}
