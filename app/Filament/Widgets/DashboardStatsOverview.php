<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total Active Employees
        $totalEmployees = Employee::where('is_active', true)->count();
        $employeesLastMonth = Employee::where('is_active', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        $employeeTrend = $employeesLastMonth > 0
            ? (($totalEmployees - $employeesLastMonth) / $employeesLastMonth) * 100
            : 0;

        // Ratings This Month
        $ratingsThisMonth = Rating::approved()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $ratingsLastMonth = Rating::approved()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $ratingTrend = $ratingsLastMonth > 0
            ? (($ratingsThisMonth - $ratingsLastMonth) / $ratingsLastMonth) * 100
            : 0;

        // Average Satisfaction
        $avgSatisfaction = Rating::approved()->avg('overall_satisfaction') ?? 0;
        $avgSatisfactionLastMonth = Rating::approved()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction') ?? 0;

        $satisfactionTrend = $avgSatisfactionLastMonth > 0
            ? (($avgSatisfaction - $avgSatisfactionLastMonth) / $avgSatisfactionLastMonth) * 100
            : 0;

        // Pending Ratings
        $pendingRatings = Rating::where('is_approved', false)
            ->where('is_flagged', false)
            ->count();

        return [
            Stat::make('Total Karyawan Aktif', $totalEmployees)
                ->description($employeeTrend >= 0 ? '+' . number_format($employeeTrend, 1) . '% dari bulan lalu' : number_format($employeeTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($employeeTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($employeeTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 12, 8, 15, 10, 18, $totalEmployees]),

            Stat::make('Rating Bulan Ini', $ratingsThisMonth)
                ->description($ratingTrend >= 0 ? '+' . number_format($ratingTrend, 1) . '% dari bulan lalu' : number_format($ratingTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($ratingTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ratingTrend >= 0 ? 'success' : 'danger')
                ->chart([45, 52, 48, 65, 58, 70, $ratingsThisMonth]),

            Stat::make('Rata-rata Kepuasan', number_format($avgSatisfaction, 2) . ' / 5.0')
                ->description($satisfactionTrend >= 0 ? '+' . number_format($satisfactionTrend, 1) . '% dari bulan lalu' : number_format($satisfactionTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($satisfactionTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($avgSatisfaction >= 4 ? 'success' : ($avgSatisfaction >= 3 ? 'warning' : 'danger'))
                ->chart([3.8, 4.1, 3.9, 4.3, 4.2, 4.5, $avgSatisfaction]),

            Stat::make('Pending Approval', $pendingRatings)
                ->description('Rating menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingRatings > 10 ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.ratings.index', ['tableFilters[is_approved][value]' => false])),
        ];
    }
}
