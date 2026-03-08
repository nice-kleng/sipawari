<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\AdminChartsWidget;
use App\Filament\Widgets\AdminRatingTableWidget;
use App\Filament\Widgets\UnitStatsWidget;
use App\Filament\Widgets\UnitChartWidget;
use App\Filament\Widgets\UnitEmployeeTableWidget;
use App\Filament\Widgets\UnitRatingTableWidget;
use App\Filament\Widgets\StafStatsWidget;
use App\Filament\Widgets\StafChartWidget;
use App\Filament\Widgets\StafRatingTableWidget;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            // ── Super Admin ─────────────────────────────────
            AdminStatsWidget::class,
            AdminChartsWidget::class,
            AdminRatingTableWidget::class,

            // ── Kepala Unit ─────────────────────────────────
            UnitStatsWidget::class,
            UnitChartWidget::class,
            UnitEmployeeTableWidget::class,
            UnitRatingTableWidget::class,

            // ── Staf ────────────────────────────────────────
            StafStatsWidget::class,
            StafChartWidget::class,
            StafRatingTableWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
