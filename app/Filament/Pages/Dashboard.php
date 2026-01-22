<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\DemographicsInsightsWidget;
use App\Filament\Widgets\EmployeePerformanceMonitor;
use App\Filament\Widgets\FlaggedRatingsWidget;
use App\Filament\Widgets\QuickActionsWidget;
use App\Filament\Widgets\RaterActivityWidget;
use App\Filament\Widgets\RatingDistributionChart;
use App\Filament\Widgets\RatingTrendsChart;
use App\Filament\Widgets\RecentRatingsTable;
use App\Filament\Widgets\ServiceQualityMetrics;
use App\Filament\Widgets\TopPerformersChart;
use App\Filament\Widgets\UnitPerformanceChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            RatingTrendsChart::class,
            TopPerformersChart::class,
            RatingDistributionChart::class,
            UnitPerformanceChart::class,
            RecentRatingsTable::class,
            FlaggedRatingsWidget::class,
            EmployeePerformanceMonitor::class,
            ServiceQualityMetrics::class,
            RaterActivityWidget::class,
            DemographicsInsightsWidget::class,
            QuickActionsWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
