<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RatingTerbaruUnitWidget extends BaseWidget
{
    protected static ?string $heading = 'Rating Terbaru Unit';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return $table->query(Rating::query()->whereRaw('1 = 0'));
        }

        $unitId = $employee->unit_id;

        return $table
            ->query(
                Rating::query()
                    ->whereHas('employee', function ($query) use ($unitId) {
                        $query->where('unit_id', $unitId);
                    })
                    ->where('is_approved', true)
                    ->with(['employee', 'employee.jabatan'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.jabatan.name')
                    ->label('Jabatan'),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('friendliness')
                    ->label('Keramahan')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('professionalism')
                    ->label('Profesional')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('service_speed')
                    ->label('Kecepatan')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
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
