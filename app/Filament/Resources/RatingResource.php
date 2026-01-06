<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingResource\Pages;
use App\Models\Rating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Penilaian';
    protected static ?string $modelLabel = 'Penilaian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Penilaian')
                ->schema([
                    Forms\Components\Select::make('employee_id')
                        ->label('Karyawan')
                        ->relationship('employee', 'name')
                        ->required()
                        ->disabled(),

                    Forms\Components\TextInput::make('rater.full_name')
                        ->label('Nama Penilai')
                        ->disabled(),

                    Forms\Components\TextInput::make('rater.decrypted_phone')
                        ->label('Telepon')
                        ->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Skor Penilaian')
                ->schema([
                    Forms\Components\TextInput::make('overall_satisfaction')
                        ->label('Kepuasan Keseluruhan')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('friendliness')
                        ->label('Keramahan')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('professionalism')
                        ->label('Profesionalisme')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('service_speed')
                        ->label('Kecepatan Layanan')
                        ->numeric()
                        ->disabled(),
                ])->columns(4),

            Forms\Components\Section::make('Komentar')
                ->schema([
                    Forms\Components\Textarea::make('comment')
                        ->label('Komentar')
                        ->rows(3)
                        ->disabled(),
                ]),

            Forms\Components\Section::make('Moderasi')
                ->schema([
                    Forms\Components\Toggle::make('is_approved')
                        ->label('Disetujui')
                        ->default(true),

                    Forms\Components\Toggle::make('is_flagged')
                        ->label('Ditandai'),

                    Forms\Components\Textarea::make('flag_reason')
                        ->label('Alasan Penandaan')
                        ->rows(2)
                        ->visible(fn($get) => $get('is_flagged')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.department')
                    ->label('Departemen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rater.full_name')
                    ->label('Penilai')
                    ->searchable(),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Kepuasan')
                    ->badge()
                    ->color(fn($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Rata-rata')
                    ->getStateUsing(fn($record) => number_format($record->average_score, 1)),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Disetujui')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_flagged')
                    ->label('Ditandai')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name'),

                Tables\Filters\SelectFilter::make('overall_satisfaction')
                    ->label('Kepuasan')
                    ->options([
                        1 => '1 Bintang',
                        2 => '2 Bintang',
                        3 => '3 Bintang',
                        4 => '4 Bintang',
                        5 => '5 Bintang',
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Status Persetujuan'),

                Tables\Filters\TernaryFilter::make('is_flagged')
                    ->label('Ditandai'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRatings::route('/'),
            'view' => Pages\ViewRating::route('/{record}'),
            'edit' => Pages\EditRating::route('/{record}/edit'),
        ];
    }
}
