<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rater;
use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class AdminStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // ── Karyawan Aktif ─────────────────────────────────────────
        $totalEmployees    = Employee::where('is_active', true)->count();
        $employeesLastMonth = Employee::where('is_active', true)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        $employeeTrend = $employeesLastMonth > 0
            ? (($totalEmployees - $employeesLastMonth) / $employeesLastMonth) * 100
            : 0;

        // ── Rating Bulan Ini ────────────────────────────────────────
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

        // ── Rata-rata Kepuasan ──────────────────────────────────────
        $avgSatisfaction         = Rating::approved()->avg('overall_satisfaction') ?? 0;
        $avgSatisfactionLastMonth = Rating::approved()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_satisfaction') ?? 0;

        $satisfactionTrend = $avgSatisfactionLastMonth > 0
            ? (($avgSatisfaction - $avgSatisfactionLastMonth) / $avgSatisfactionLastMonth) * 100
            : 0;

        // ── Pending Approval ────────────────────────────────────────
        $pendingRatings = Rating::where('is_approved', false)
            ->where('is_flagged', false)
            ->count();

        // ── Penilai & Aktivitas Hari Ini ───────────────────────────
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

        $ratingsToday = Rating::approved()
            ->whereDate('created_at', today())
            ->count();

        $avgPerDay = now()->day > 0
            ? round($ratingsThisMonth / now()->day, 1)
            : 0;

        return [
            Stat::make('Total Karyawan Aktif', $totalEmployees)
                ->description($employeeTrend >= 0
                    ? '+' . number_format($employeeTrend, 1) . '% dari bulan lalu'
                    : number_format($employeeTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($employeeTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($employeeTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 12, 8, 15, 10, 18, $totalEmployees]),

            Stat::make('Rating Bulan Ini', $ratingsThisMonth)
                ->description($ratingTrend >= 0
                    ? '+' . number_format($ratingTrend, 1) . '% dari bulan lalu'
                    : number_format($ratingTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($ratingTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ratingTrend >= 0 ? 'success' : 'danger')
                ->chart([45, 52, 48, 65, 58, 70, $ratingsThisMonth]),

            Stat::make('Rata-rata Kepuasan', number_format($avgSatisfaction, 2) . ' / 5.0')
                ->description($satisfactionTrend >= 0
                    ? '+' . number_format($satisfactionTrend, 1) . '% dari bulan lalu'
                    : number_format($satisfactionTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($satisfactionTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($avgSatisfaction >= 4 ? 'success' : ($avgSatisfaction >= 3 ? 'warning' : 'danger'))
                ->chart([3.8, 4.1, 3.9, 4.3, 4.2, 4.5, $avgSatisfaction]),

            Stat::make('Pending Approval', $pendingRatings)
                ->description('Rating menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingRatings > 10 ? 'warning' : 'gray')
                ->url(route('filament.admin.resources.ratings.index', [
                    'tableFilters[is_approved][value]' => false,
                ])),

            Stat::make('Penilai Aktif Bulan Ini', $ratersThisMonth)
                ->description($raterTrend >= 0
                    ? '+' . number_format($raterTrend, 1) . '% dari bulan lalu'
                    : number_format($raterTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($raterTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($raterTrend >= 0 ? 'success' : 'warning')
                ->chart([15, 22, 18, 28, 25, 35, $ratersThisMonth]),

            Stat::make('Rating Hari Ini', $ratingsToday)
                ->description('Rata-rata ' . $avgPerDay . ' per hari bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success')
                ->chart([5, 8, 6, 12, 9, 15, $ratingsToday]),
        ];
    }
}
