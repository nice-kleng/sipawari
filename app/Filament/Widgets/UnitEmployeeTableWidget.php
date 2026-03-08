<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UnitEmployeeTableWidget extends BaseWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?int $sort = 11;
    protected int | string | array $columnSpan = 'full';

    // Mode: 'all' | 'top' | 'alert'
    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        return [
            'all'   => 'Semua Karyawan',
            'top'   => 'Top 5 Performers',
            'alert' => 'Alert Rating Rendah',
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->filter) {
            'top'   => $this->getTopTable($table),
            'alert' => $this->getAlertTable($table),
            default => $this->getAllTable($table),
        };
    }

    private function baseQuery(Builder $query, array $teamIds): Builder
    {
        return $query
            ->whereIn('id', $teamIds)
            ->where('is_active', true)
            ->withCount(['ratings as approved_ratings_count' => fn($q) => $q->where('is_approved', true)])
            ->withAvg(['ratings as avg_rating' => fn($q) => $q->where('is_approved', true)], 'overall_satisfaction');
    }

    private function baseColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('employee_code')
                ->label('NIP')->searchable()->sortable(),

            Tables\Columns\ImageColumn::make('photo')
                ->label('Foto')->circular()
                ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=User'),

            Tables\Columns\TextColumn::make('name')
                ->label('Nama Karyawan')->searchable()->sortable()->weight('bold'),

            Tables\Columns\TextColumn::make('jabatan.name')
                ->label('Jabatan'),

            Tables\Columns\TextColumn::make('approved_ratings_count')
                ->label('Total Rating')->alignCenter()->sortable(),

            Tables\Columns\TextColumn::make('avg_rating')
                ->label('Rata-rata')
                ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : '-')
                ->sortable()->badge()
                ->color(fn($state) => match (true) {
                    $state >= 4.5 => 'success',
                    $state >= 4.0 => 'primary',
                    $state >= 3.0 => 'warning',
                    $state > 0    => 'danger',
                    default       => 'gray',
                }),
        ];
    }

    private function getAllTable(Table $table): Table
    {
        $employee = auth()->user()->employee;
        $teamIds = $employee ? $employee->allSubordinateIds() : [];

        return $table
            ->heading('Performa Karyawan Tim')
            ->query($this->baseQuery(Employee::query(), $teamIds))
            ->columns($this->baseColumns())
            ->defaultSort('avg_rating', 'desc');
    }

    private function getTopTable(Table $table): Table
    {
        $employee = auth()->user()->employee;
        $teamIds = $employee ? $employee->allSubordinateIds() : [];

        return $table
            ->heading('Top 5 Performers Tim')
            ->query(
                $this->baseQuery(Employee::query(), $teamIds)
                    ->having('approved_ratings_count', '>=', 5)
                    ->orderByDesc('avg_rating')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(fn($rowLoop) => $rowLoop->iteration)
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'success', 2 => 'info', 3 => 'warning', default => 'gray',
                    }),
                ...$this->baseColumns(),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada karyawan dengan min. 5 rating')
            ->emptyStateIcon('heroicon-o-star');
    }

    private function getAlertTable(Table $table): Table
    {
        $employee = auth()->user()->employee;
        $teamIds = $employee ? $employee->allSubordinateIds() : [];

        return $table
            ->heading('Alert: Karyawan Rating Rendah')
            ->query(
                $this->baseQuery(Employee::query(), $teamIds)
                    ->having('avg_rating', '<', 3.0)
                    ->having('approved_ratings_count', '>=', 3)
                    ->orderBy('avg_rating')
            )
            ->columns([
                ...$this->baseColumns(),
                Tables\Columns\TextColumn::make('status_alert')
                    ->label('Status Alert')
                    ->state(fn(Employee $e) => match (true) {
                        $e->avg_rating < 2.0 => 'Kritis',
                        $e->avg_rating < 2.5 => 'Sangat Rendah',
                        default              => 'Rendah',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Kritis'        => 'danger',
                        'Sangat Rendah' => 'warning',
                        default         => 'info',
                    }),
            ])
            ->emptyStateHeading('Tidak ada karyawan dengan rating rendah')
            ->emptyStateDescription('Semua karyawan memiliki performa yang baik!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function canView(): bool
    {
        return static::canViewShield()
            && auth()->user()->hasPermissionTo('view_team_dashboard')
            && auth()->user()->employee !== null;
    }
}
