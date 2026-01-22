<?php

namespace App\Filament\Widgets;

use App\Models\Rater;
use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RaterActivityWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        // Unique raters this month
        $ratersThisMonth = Rating::approved()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct('rater_id')
            ->count('rater_id');

        $ratersLastMonth = Rating::approved()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->distinct('rater_id')
            ->count('rater_id');

        $raterTrend = $ratersLastMonth > 0
            ? (($ratersThisMonth - $ratersLastMonth) / $ratersLastMonth) * 100
            : 0;

        // Total unique raters
        $totalRaters = Rater::count();

        // Ratings today
        $ratingsToday = Rating::approved()
            ->whereDate('created_at', today())
            ->count();

        // Average ratings per day this month
        $daysInMonth = now()->day;
        $ratingsThisMonth = Rating::approved()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $avgPerDay = $daysInMonth > 0 ? round($ratingsThisMonth / $daysInMonth, 1) : 0;

        return [
            Stat::make('Penilai Aktif Bulan Ini', $ratersThisMonth)
                ->description($raterTrend >= 0 ? '+' . number_format($raterTrend, 1) . '% dari bulan lalu' : number_format($raterTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($raterTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($raterTrend >= 0 ? 'success' : 'warning')
                ->chart([15, 22, 18, 28, 25, 35, $ratersThisMonth]),

            Stat::make('Total Penilai Terdaftar', $totalRaters)
                ->description('Sejak awal sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Rating Hari Ini', $ratingsToday)
                ->description('Rata-rata ' . $avgPerDay . ' per hari bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success')
                ->chart([5, 8, 6, 12, 9, 15, $ratingsToday]),
        ];
    }
}
