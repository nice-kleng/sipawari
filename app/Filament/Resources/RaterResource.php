<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaterResource\Pages;
use App\Models\Rater;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RaterResource extends Resource
{
    protected static ?string $model = Rater::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Raters';

    protected static ?string $navigationGroup = 'Rating Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Full Name'),

                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
                            ->native(false)
                            ->label('Gender'),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Birth Date')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->maxDate(now()),

                        Forms\Components\Placeholder::make('phone_display')
                            ->label('Phone Number')
                            ->content(fn(Rater $record): string => $record->decrypted_phone ?? 'Encrypted')
                            ->visibleOn(['view', 'edit']),
                    ])->columns(2),

                Forms\Components\Section::make('Visit Information')
                    ->schema([
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->native(false)
                            ->displayFormat('d M Y'),

                        Forms\Components\TextInput::make('service_unit')
                            ->maxLength(255)
                            ->label('Service Unit')
                            ->helperText('Which unit/department did they visit?'),

                        Forms\Components\TextInput::make('relationship_to_patient')
                            ->maxLength(255)
                            ->label('Relationship to Patient')
                            ->helperText('e.g., Self, Family, Friend'),

                        Forms\Components\Toggle::make('consent_given')
                            ->label('Consent Given')
                            ->helperText('Data processing consent')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Privacy & Security')
                    ->schema([
                        Forms\Components\Placeholder::make('nik_hash')
                            ->label('NIK Hash')
                            ->content(fn(Rater $record): string => substr($record->nik_hash, 0, 20) . '...')
                            ->helperText('NIK is hashed for privacy'),

                        Forms\Components\Placeholder::make('phone_encrypted')
                            ->label('Phone Encrypted')
                            ->content(fn(Rater $record): string => substr($record->phone_encrypted, 0, 20) . '...')
                            ->helperText('Phone is encrypted for privacy'),
                    ])
                    ->columns(2)
                    ->visibleOn('view')
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\Placeholder::make('ratings_count')
                            ->label('Total Ratings Given')
                            ->content(fn(Rater $record): string => (string) $record->ratings()->count()),

                        Forms\Components\Placeholder::make('approved_ratings_count')
                            ->label('Approved Ratings')
                            ->content(fn(Rater $record): string => (string) $record->ratings()->where('is_approved', true)->count()),

                        Forms\Components\Placeholder::make('average_rating_given')
                            ->label('Average Rating Given')
                            ->content(fn(Rater $record): string => number_format($record->ratings()->avg('overall_satisfaction') ?? 0, 2)),
                    ])
                    ->columns(3)
                    ->visibleOn(['view', 'edit'])
                    ->collapsible(),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('First Rating Date')
                            ->content(fn(Rater $record): ?string => $record->created_at?->format('d M Y H:i:s')),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn(Rater $record): ?string => $record->updated_at?->format('d M Y H:i:s')),
                    ])
                    ->columns(2)
                    ->visibleOn(['view', 'edit'])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'male' => 'primary',
                        'female' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('birth_date')
                    ->label('Birth Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('decrypted_phone')
                    ->label('Phone')
                    ->getStateUsing(fn(Rater $record) => $record->decrypted_phone ?? 'Encrypted')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('service_unit')
                    ->label('Service Unit')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('relationship_to_patient')
                    ->label('Relationship')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ratings_count')
                    ->counts('ratings')
                    ->label('Ratings')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('consent_given')
                    ->label('Consent')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('First Rating')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('service_unit')
                    ->options(function () {
                        return Rater::whereNotNull('service_unit')
                            ->distinct()
                            ->pluck('service_unit', 'service_unit');
                    })
                    ->searchable()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('consent_given')
                    ->label('Consent Status')
                    ->boolean()
                    ->trueLabel('Consent Given')
                    ->falseLabel('No Consent')
                    ->native(false),

                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('visit_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('visit_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('view_ratings')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->url(fn(Rater $record) => route('filament.admin.resources.ratings.index', ['rater_id' => $record->id]))
                        ->label('View Ratings'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRaters::route('/'),
            'create' => Pages\CreateRater::route('/create'),
            'view' => Pages\ViewRater::route('/{record}'),
            'edit' => Pages\EditRater::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
