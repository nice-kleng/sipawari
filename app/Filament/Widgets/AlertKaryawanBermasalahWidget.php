<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertKaryawanBermasalahWidget extends BaseWidget
{
    protected static ?string $heading = 'Alert: Karyawan Rating Rendah';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return $table->query(Employee::query()->whereRaw('1 = 0'));
        }

        $unitId = $employee->unit_id;

        return $table
            ->query(
                Employee::query()
                    ->where('unit_id', $unitId)
                    ->where('is_active', true)
                    ->withCount(['ratings as approved_ratings_count' => function ($query) {
                        $query->where('is_approved', true);
                    }])
                    ->withAvg(['ratings as avg_rating' => function ($query) {
                        $query->where('is_approved', true);
                    }], 'overall_satisfaction')
                    ->having('avg_rating', '<', 3.0)
                    ->having('approved_ratings_count', '>=', 3) // Min 3 ratings
                    ->orderBy('avg_rating')
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Kode')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=User'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Jabatan'),

                Tables\Columns\TextColumn::make('approved_ratings_count')
                    ->label('Total Rating')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Rata-rata Rating')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status Alert')
                    ->state(fn($record) => match (true) {
                        $record->avg_rating < 2.0 => 'Kritis',
                        $record->avg_rating < 2.5 => 'Sangat Rendah',
                        default => 'Rendah'
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Kritis' => 'danger',
                        'Sangat Rendah' => 'warning',
                        default => 'info'
                    }),
            ])
            ->emptyStateHeading('Tidak ada karyawan dengan rating rendah')
            ->emptyStateDescription('Semua karyawan memiliki performa yang baik!')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user->hasRole('kepala_unit')) {
            return false;
        }

        return $user->employee !== null;
    }
}
