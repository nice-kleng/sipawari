<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RatingSayaStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return [];
        }

        // Total rating approved
        $totalRatings = $employee->ratings()
            ->where('is_approved', true)
            ->count();

        // Average rating
        $avgRating = $employee->ratings()
            ->where('is_approved', true)
            ->avg('overall_satisfaction');

        // Rating bulan ini
        $ratingsThisMonth = $employee->ratings()
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Rating bulan lalu untuk comparison
        $lastMonthRating = $employee->ratings()
            ->where('is_approved', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction');

        $trend = $lastMonthRating > 0
            ? (($avgRating - $lastMonthRating) / $lastMonthRating) * 100
            : 0;

        // Rating tertinggi
        $highestRating = $employee->ratings()
            ->where('is_approved', true)
            ->max('overall_satisfaction');

        return [
            Stat::make('Total Rating Diterima', $totalRatings)
                ->description('Rating yang telah disetujui')
                ->descriptionIcon('heroicon-o-star')
                ->color('primary'),

            Stat::make('Rata-rata Rating', number_format($avgRating ?? 0, 2))
                ->description($trend >= 0 ? "Naik " . number_format($trend, 1) . "%" : "Turun " . number_format(abs($trend), 1) . "%")
                ->descriptionIcon($trend >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make('Rating Bulan Ini', $ratingsThisMonth)
                ->description('Penilaian di bulan ini')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Rating Tertinggi', number_format($highestRating ?? 0, 1))
                ->description('Pencapaian terbaik')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),
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
