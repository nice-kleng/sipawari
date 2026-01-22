<?php

namespace App\Filament\Widgets;

use App\Models\Unit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class KomparasiUnitWidget extends BaseWidget
{
    protected static ?string $heading = 'Peringkat Unit';
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return $table->query(Unit::query()->whereRaw('1 = 0'));
        }

        $myUnitId = $employee->unit_id;

        return $table
            ->query(
                Unit::query()
                    ->select('units.*')
                    ->leftJoin('employees', 'units.id', '=', 'employees.unit_id')
                    ->leftJoin('ratings', function ($join) {
                        $join->on('employees.id', '=', 'ratings.employee_id')
                            ->where('ratings.is_approved', true);
                    })
                    ->groupBy('units.id')
                    ->selectRaw('COALESCE(AVG(ratings.overall_satisfaction), 0) as avg_rating')
                    ->selectRaw('COUNT(DISTINCT employees.id) as employee_count')
                    ->selectRaw('COUNT(ratings.id) as total_ratings')
                    ->orderByDesc('avg_rating')
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('Peringkat')
                    ->state(function ($rowLoop) {
                        return $rowLoop->iteration;
                    })
                    ->badge()
                    ->color(
                        fn($state, $record) =>
                        $record->id === $myUnitId
                            ? 'success'
                            : ($state <= 3 ? 'primary' : 'gray')
                    ),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Unit')
                    ->weight(fn($record) => $record->id === $myUnitId ? 'bold' : 'normal')
                    ->color(fn($record) => $record->id === $myUnitId ? 'success' : null),

                Tables\Columns\TextColumn::make('employee_count')
                    ->label('Karyawan')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_ratings')
                    ->label('Total Rating')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\IconColumn::make('is_my_unit')
                    ->label('Unit Saya')
                    ->boolean()
                    ->state(fn($record) => $record->id === $myUnitId)
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('')
                    ->trueColor('warning'),
            ])
            ->paginated(false);
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
