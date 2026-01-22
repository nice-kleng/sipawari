<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RatingTerbaruSayaWidget extends BaseWidget
{
    protected static ?string $heading = 'Rating Terbaru Saya';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return $table->query(Rating::query()->whereRaw('1 = 0'));
        }

        return $table
            ->query(
                Rating::query()
                    ->where('employee_id', $employee->id)
                    ->where('is_approved', true)
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
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
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10);
    }

    public static function canView(): bool
    {
        $user = auth()->user();

        if (!$user->hasRole('karyawan')) {
            return false;
        }

        return $user->employee !== null;
    }
}
