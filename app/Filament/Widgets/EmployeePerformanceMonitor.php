<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class EmployeePerformanceMonitor extends BaseWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Monitor Performa Karyawan')
            ->description('Karyawan dengan rating menurun atau di bawah standar')
            ->query(
                Employee::query()
                    ->where('is_active', true)
                    ->withCount(['ratings as approved_ratings_count' => function ($query) {
                        $query->where('is_approved', true);
                    }])
                    ->withAvg(['ratings as avg_rating' => function ($query) {
                        $query->where('is_approved', true);
                    }], 'overall_satisfaction')
                    ->withAvg(['ratings as avg_rating_this_month' => function ($query) {
                        $query->where('is_approved', true)
                            ->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                    }], 'overall_satisfaction')
                    ->withAvg(['ratings as avg_rating_last_month' => function ($query) {
                        $query->where('is_approved', true)
                            ->whereMonth('created_at', now()->subMonth()->month)
                            ->whereYear('created_at', now()->subMonth()->year);
                    }], 'overall_satisfaction')
                    ->having('approved_ratings_count', '>', 0)
                    ->orderBy('avg_rating', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=NA&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->description(
                        fn(Employee $record): string =>
                        $record->employee_code ?? '-'
                    ),

                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Posisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Rata-rata Total')
                    ->numeric(2)
                    ->sortable()
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'info',
                        $state >= 3.5 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('avg_rating_this_month')
                    ->label('Bulan Ini')
                    ->numeric(2)
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'info',
                        $state >= 3.5 => 'warning',
                        $state === null => 'gray',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : 'N/A'),

                Tables\Columns\TextColumn::make('avg_rating_last_month')
                    ->label('Bulan Lalu')
                    ->numeric(2)
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 2) : 'N/A'),

                Tables\Columns\IconColumn::make('trend')
                    ->label('Tren')
                    ->state(function (Employee $record) {
                        if (!$record->avg_rating_this_month || !$record->avg_rating_last_month) {
                            return 'neutral';
                        }
                        if ($record->avg_rating_this_month > $record->avg_rating_last_month) {
                            return 'up';
                        } elseif ($record->avg_rating_this_month < $record->avg_rating_last_month) {
                            return 'down';
                        }
                        return 'neutral';
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'up' => 'heroicon-o-arrow-trending-up',
                        'down' => 'heroicon-o-arrow-trending-down',
                        default => 'heroicon-o-minus',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'up' => 'success',
                        'down' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('approved_ratings_count')
                    ->label('Total Rating')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_rating')
                    ->label('Rating < 3.5')
                    ->query(fn(Builder $query): Builder => $query->having('avg_rating', '<', 3.5)),

                Tables\Filters\Filter::make('declining')
                    ->label('Menurun Bulan Ini')
                    ->query(function (Builder $query): Builder {
                        return $query->whereRaw('avg_rating_this_month < avg_rating_last_month');
                    }),

                Tables\Filters\SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Employee $record): string => route('filament.admin.resources.employees.view', $record)),
            ]);
    }
}
