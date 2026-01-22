<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FlaggedRatingsWidget extends BaseWidget
{
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rating yang Di-Flag (Perlu Review)')
            ->query(
                Rating::query()
                    ->with(['employee.unit', 'rater'])
                    ->where('is_flagged', true)
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->description(
                        fn(Rating $record): string =>
                        $record->employee->unit?->name ?? '-'
                    ),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Rating')
                    ->formatStateUsing(fn(string $state): string => str_repeat('⭐', (int) $state)),

                Tables\Columns\TextColumn::make('flag_reason')
                    ->label('Alasan Flag')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
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

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\Action::make('unflag')
                    ->label('Hapus Flag')
                    ->icon('heroicon-o-x-mark')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Rating $record) {
                        $record->update([
                            'is_flagged' => false,
                            'flag_reason' => null,
                        ]);
                    }),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Rating $record) {
                        $record->update(['is_approved' => true]);
                    })
                    ->visible(fn(Rating $record) => !$record->is_approved),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation(),
            ])
            ->emptyStateHeading('Tidak ada rating yang di-flag')
            ->emptyStateDescription('Semua rating dalam kondisi baik.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
