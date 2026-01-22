<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PerformaKaryawanUnitWidget extends BaseWidget
{
    protected static ?int $sort = 2;
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
                    ->orderByDesc('avg_rating')
            )
            ->columns([
                Tables\Columns\TextColumn::make('employee_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Jabatan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approved_ratings_count')
                    ->label('Total Rating')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : '-')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        $state < 3.0 => 'danger',
                        default => 'gray'
                    }),
            ])
            ->defaultSort('avg_rating', 'desc');
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
