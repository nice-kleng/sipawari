<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RatingsRelationManager extends RelationManager
{
    protected static string $relationship = 'ratings';

    protected static ?string $title = 'Employee Ratings';

    protected static ?string $icon = 'heroicon-o-star';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ])->columns(2),

                Forms\Components\Textarea::make('comment')
                    ->rows(3)
                    ->maxLength(1000)
                    ->label('Comment')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Approved')
                            ->default(false),

                        Forms\Components\Toggle::make('is_flagged')
                            ->label('Flagged')
                            ->reactive(),

                        Forms\Components\Textarea::make('flag_reason')
                            ->label('Flag Reason')
                            ->visible(fn(Forms\Get $get) => $get('is_flagged'))
                            ->required(fn(Forms\Get $get) => $get('is_flagged')),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('professionalism')
                    ->label('Professional')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_speed')
                    ->label('Speed')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_score')
                    ->label('Average')
                    ->getStateUsing(fn($record) => number_format($record->average_score, 2))
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn($record) => !$record->is_approved)
                        ->requiresConfirmation()
                        ->action(fn($record) => $record->update(['is_approved' => true])),
                    Tables\Actions\Action::make('unapprove')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->visible(fn($record) => $record->is_approved)
                        ->requiresConfirmation()
                        ->action(fn($record) => $record->update(['is_approved' => false])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_approved' => true])),
                    Tables\Actions\BulkAction::make('unapprove')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn($records) => $records->each->update(['is_approved' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['rater']));
    }
}
