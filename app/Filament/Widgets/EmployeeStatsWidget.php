<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Only show for employees
        if (!$user->hasRole('karyawan') || !$user->employee) {
            return [];
        }

        $employee = $user->employee;
        $ratings = Rating::where('employee_id', $employee->id)
            ->where('is_approved', true);

        $totalRatings = $ratings->count();
        $averageRating = $ratings->avg('overall_satisfaction') ?? 0;
        $thisMonthRatings = $ratings->clone()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Calculate rating breakdown
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $ratings->clone()->where('overall_satisfaction', $i)->count();
            $ratingBreakdown[$i] = $totalRatings > 0
                ? round(($count / $totalRatings) * 100, 1)
                : 0;
        }

        return [
            Stat::make('Total Penilaian Saya', $totalRatings)
                ->description('Penilaian yang disetujui')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            Stat::make('Rating Rata-rata', number_format($averageRating, 2))
                ->description('Dari skala 1-5')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($averageRating >= 4 ? 'success' : ($averageRating >= 3 ? 'warning' : 'danger')),

            Stat::make('Penilaian Bulan Ini', $thisMonthRatings)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Rating 5 Bintang', $ratingBreakdown[5] . '%')
                ->description('Dari total penilaian')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('karyawan') ?? false;
    }
}
