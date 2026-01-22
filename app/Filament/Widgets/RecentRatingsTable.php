<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentRatingsTable extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Rating Terbaru')
            ->query(
                Rating::query()
                    ->with(['employee.unit', 'employee.jabatan', 'rater'])
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->description(
                        fn(Rating $record): string => ($record->employee->unit?->name ?? '-') . ' | ' .
                            ($record->employee->jabatan?->name ?? '-')
                    ),

                Tables\Columns\TextColumn::make('rater.full_name')
                    ->label('Penilai')
                    ->searchable(),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')
                    ->badge()
                    ->color(fn(string $state): string => match ((int) $state) {
                        5 => 'success',
                        4 => 'info',
                        3 => 'warning',
                        2 => 'danger',
                        1 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => str_repeat('⭐', (int) $state)),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_flagged')
                    ->label('Flag')
                    ->boolean()
                    ->trueIcon('heroicon-o-flag')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Status')
                    ->options([
                        '1' => 'Approved',
                        '0' => 'Pending',
                    ]),

                Tables\Filters\SelectFilter::make('is_flagged')
                    ->label('Flagged')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Rating $record) => $record->update(['is_approved' => true]))
                    ->visible(fn(Rating $record) => !$record->is_approved),

                Tables\Actions\Action::make('flag')
                    ->icon('heroicon-o-flag')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('flag_reason')
                            ->label('Alasan Flag')
                            ->required(),
                    ])
                    ->action(function (Rating $record, array $data) {
                        $record->update([
                            'is_flagged' => true,
                            'flag_reason' => $data['flag_reason'],
                        ]);
                    })
                    ->visible(fn(Rating $record) => !$record->is_flagged),

                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Rating $record): string => route('filament.admin.resources.ratings.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
