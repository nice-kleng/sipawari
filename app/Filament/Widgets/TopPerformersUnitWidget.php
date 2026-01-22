<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopPerformersUnitWidget extends BaseWidget
{
    protected static ?string $heading = 'Top 5 Performers Unit';
    protected static ?int $sort = 4;

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
                    ->having('approved_ratings_count', '>=', 5) // Min 5 ratings
                    ->orderByDesc('avg_rating')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($rowLoop) {
                        return $rowLoop->iteration;
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        default => 'gray'
                    }),

                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=User'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Jabatan'),

                Tables\Columns\TextColumn::make('approved_ratings_count')
                    ->label('Total Rating')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn($state) => number_format($state, 2))
                    ->badge()
                    ->color('success'),
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
