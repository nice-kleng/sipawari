<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class UnitRatingTableWidget extends BaseWidget
{
    use HasWidgetShield {
        canView as canViewShield;
    }

    protected static ?string $heading = 'Rating Terbaru di Unit';
    protected static ?int $sort = 13;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return $table->query(Rating::query()->whereRaw('1 = 0'));
        }

        $subordinateIds = $employee->allSubordinateIds();
        $teamIds = array_merge([$employee->id], $subordinateIds);

        return $table
            ->query(
                Rating::query()
                    ->whereIn('employee_id', $teamIds)
                    ->where('is_approved', true)
                    ->with(['employee', 'employee.jabatan'])
                    ->latest()
            )
            ->heading('Rating Terbaru di Tim')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')->dateTime('d M Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')->searchable(),

                Tables\Columns\TextColumn::make('employee.jabatan.name')
                    ->label('Jabatan'),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default     => 'danger',
                    }),

                Tables\Columns\TextColumn::make('friendliness')
                    ->label('Keramahan')->alignCenter(),

                Tables\Columns\TextColumn::make('professionalism')
                    ->label('Profesional')->alignCenter(),

                Tables\Columns\TextColumn::make('service_speed')
                    ->label('Kecepatan')->alignCenter(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success', $state >= 3 => 'warning', default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')->limit(30)
                    ->tooltip(fn(Tables\Columns\TextColumn $col) => strlen($col->getState()) > 30 ? $col->getState() : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
    }

    public static function canView(): bool
    {
        return static::canViewShield()
            && auth()->user()->hasPermissionTo('view_team_dashboard')
            && auth()->user()->employee !== null;
    }
}
