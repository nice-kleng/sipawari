<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingResource\Pages;
use App\Models\Rating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Ratings';

    protected static ?string $navigationGroup = 'Rating Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rating Information')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Employee')
                            ->disabled(fn(string $context) => $context === 'edit'),

                        Forms\Components\Select::make('rater_id')
                            ->relationship('rater', 'full_name')
                            ->searchable()
                            ->preload()
                            ->label('Rater')
                            ->disabled(fn(string $context) => $context === 'edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Rating Scores')
                    ->schema([
                        Forms\Components\Select::make('overall_satisfaction')
                            ->options([
                                1 => '1 - Very Poor',
                                2 => '2 - Poor',
                                3 => '3 - Average',
                                4 => '4 - Good',
                                5 => '5 - Excellent',
                            ])
                            ->required()
                            ->native(false)
                            ->label('Overall Satisfaction'),

                        Forms\Components\Select::make('friendliness')
                            ->options([
                                1 => '1 - Very Poor',
                                2 => '2 - Poor',
                                3 => '3 - Average',
                                4 => '4 - Good',
                                5 => '5 - Excellent',
                            ])
                            ->required()
                            ->native(false)
                            ->label('Friendliness'),

                        Forms\Components\Select::make('professionalism')
                            ->options([
                                1 => '1 - Very Poor',
                                2 => '2 - Poor',
                                3 => '3 - Average',
                                4 => '4 - Good',
                                5 => '5 - Excellent',
                            ])
                            ->required()
                            ->native(false)
                            ->label('Professionalism'),

                        Forms\Components\Select::make('service_speed')
                            ->options([
                                1 => '1 - Very Poor',
                                2 => '2 - Poor',
                                3 => '3 - Average',
                                4 => '4 - Good',
                                5 => '5 - Excellent',
                            ])
                            ->required()
                            ->native(false)
                            ->label('Service Speed'),

                        Forms\Components\Placeholder::make('average_score')
                            ->label('Average Score')
                            ->content(fn(Rating $record): string => number_format($record->average_score ?? 0, 2))
                            ->visibleOn('edit'),
                    ])->columns(2),

                Forms\Components\Section::make('Comment & Feedback')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->rows(4)
                            ->maxLength(1000)
                            ->label('Comment')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Approved')
                            ->helperText('Only approved ratings will be shown publicly')
                            ->default(false),

                        Forms\Components\Toggle::make('is_flagged')
                            ->label('Flagged')
                            ->helperText('Mark this rating for review')
                            ->reactive()
                            ->default(false),

                        Forms\Components\Textarea::make('flag_reason')
                            ->label('Flag Reason')
                            ->rows(3)
                            ->visible(fn(Forms\Get $get) => $get('is_flagged'))
                            ->required(fn(Forms\Get $get) => $get('is_flagged')),
                    ])->columns(2),

                Forms\Components\Section::make('Technical Information')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),

                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->rows(2)
                            ->disabled(),
                    ])
                    ->columns(1)
                    ->visibleOn('view')
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn(Rating $record): ?string => $record->created_at?->format('d M Y H:i:s')),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Updated At')
                            ->content(fn(Rating $record): ?string => $record->updated_at?->format('d M Y H:i:s')),
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Rating $record) => $record->employee?->employee_code)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('employee.unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rater.full_name')
                    ->label('Rater')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('overall_satisfaction')
                    ->label('Overall')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('friendliness')
                    ->label('Friendly')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('professionalism')
                    ->label('Professional')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('service_speed')
                    ->label('Speed')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Average')
                    ->getStateUsing(fn(Rating $record) => number_format($record->average_score, 2))
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comment')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Approved')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('is_flagged')
                    ->label('Flagged')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-flag')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Employee')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('unit')
                    ->relationship('employee.unit', 'name')
                    ->label('Unit')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Approval Status')
                    ->boolean()
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_flagged')
                    ->label('Flag Status')
                    ->boolean()
                    ->trueLabel('Flagged')
                    ->falseLabel('Not Flagged')
                    ->native(false),

                Tables\Filters\Filter::make('overall_satisfaction')
                    ->form([
                        Forms\Components\Select::make('min')
                            ->label('Minimum Rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->native(false),
                        Forms\Components\Select::make('max')
                            ->label('Maximum Rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min'],
                                fn(Builder $query, $min): Builder => $query->where('overall_satisfaction', '>=', $min),
                            )
                            ->when(
                                $data['max'],
                                fn(Builder $query, $max): Builder => $query->where('overall_satisfaction', '<=', $max),
                            );
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(Rating $record) => !$record->is_approved)
                        ->requiresConfirmation()
                        ->action(fn(Rating $record) => $record->update(['is_approved' => true]))
                        ->successNotificationTitle('Rating approved'),
                    Tables\Actions\Action::make('unapprove')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn(Rating $record) => $record->is_approved)
                        ->requiresConfirmation()
                        ->action(fn(Rating $record) => $record->update(['is_approved' => false]))
                        ->successNotificationTitle('Rating unapproved'),
                    Tables\Actions\Action::make('flag')
                        ->icon('heroicon-o-flag')
                        ->color('danger')
                        ->visible(fn(Rating $record) => !$record->is_flagged)
                        ->form([
                            Forms\Components\Textarea::make('flag_reason')
                                ->required()
                                ->label('Reason for flagging'),
                        ])
                        ->action(function (Rating $record, array $data) {
                            $record->update([
                                'is_flagged' => true,
                                'flag_reason' => $data['flag_reason'],
                            ]);
                        })
                        ->successNotificationTitle('Rating flagged'),
                    Tables\Actions\Action::make('unflag')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn(Rating $record) => $record->is_flagged)
                        ->requiresConfirmation()
                        ->action(fn(Rating $record) => $record->update(['is_flagged' => false, 'flag_reason' => null]))
                        ->successNotificationTitle('Flag removed'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unapprove')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_approved' => false]))
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListRatings::route('/'),
            'create' => Pages\CreateRating::route('/create'),
            'view' => Pages\ViewRating::route('/{record}'),
            'edit' => Pages\EditRating::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_approved', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('is_approved', false)->count();
        return $count > 0 ? 'warning' : 'success';
    }
}
