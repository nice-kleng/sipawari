<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEmployees = Employee::where('is_active', true)->count();
        $totalRatings = Rating::where('is_approved', true)->count();
        $averageRating = Rating::where('is_approved', true)->avg('overall_satisfaction');
        $ratingsThisMonth = Rating::where('is_approved', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total Karyawan Aktif', $totalEmployees)
                ->description('Karyawan yang dapat dinilai')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Penilaian', $totalRatings)
                ->description('Penilaian yang disetujui')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            Stat::make('Rata-rata Rating', number_format($averageRating ?? 0, 2))
                ->description('Dari skala 1-5')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),

            Stat::make('Penilaian Bulan Ini', $ratingsThisMonth)
                ->description('Total bulan ' . now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }
}
