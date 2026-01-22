<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers\RatingsRelationManager;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Karyawan';
    protected static ?string $modelLabel = 'Karyawan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pengguna')
                ->schema([
                    Forms\Components\TextInput::make('user.email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true),

                    Forms\Components\TextInput::make('user.password')
                        ->label('Password')
                        ->password()
                        ->required(fn($context) => $context === 'create')
                        ->dehydrated(fn($state) => filled($state))
                        ->minLength(8),
                ])->columns(2),

            Forms\Components\Section::make('Data Karyawan')
                ->schema([
                    Forms\Components\TextInput::make('employee_code')
                        ->label('NIP')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\FileUpload::make('photo')
                        ->label('Foto')
                        ->image()
                        ->directory('employee-photos')
                        ->visibility('public')
                        ->maxSize(2048),

                    Forms\Components\Select::make('position_id')
                        ->relationship('jabatan', 'name')
                        ->label('Jabatan')
                        ->required(),

                    Forms\Components\Select::make('unit_id')
                        ->relationship('unit', 'name')
                        ->label('Departemen / Unit')
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->required(),
                ])->columns(2),
            Forms\Components\Section::make('QR Code & UUID')
                ->schema([
                    Forms\Components\Placeholder::make('uuid')
                        ->label('UUID')
                        ->content(fn(Employee $record): string => $record->uuid ?? 'Will be generated automatically'),

                    Forms\Components\Placeholder::make('rating_url')
                        ->label('Rating URL')
                        ->content(fn(Employee $record): string => $record->rating_url ?? 'Available after creation'),

                    Forms\Components\Placeholder::make('qr_code')
                        ->label('QR Code')
                        ->content(function (Employee $record): string {
                            if ($record->qr_code_path && Storage::disk('public')->exists($record->qr_code_path)) {
                                return '<img src="' . Storage::url($record->qr_code_path) . '" alt="QR Code" class="w-32 h-32">';
                            }
                            return 'QR Code will be generated after creation';
                        })
                        ->extraAttributes(['class' => 'qr-code-preview'])
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->visibleOn('edit')
                ->collapsible(),
            Forms\Components\Section::make('Timestamps')
                ->schema([
                    Forms\Components\Placeholder::make('created_at')
                        ->label('Created At')
                        ->content(fn(Employee $record): ?string => $record->created_at?->diffForHumans()),

                    Forms\Components\Placeholder::make('updated_at')
                        ->label('Updated At')
                        ->content(fn(Employee $record): ?string => $record->updated_at?->diffForHumans()),

                    Forms\Components\Placeholder::make('deleted_at')
                        ->label('Deleted At')
                        ->content(fn(Employee $record): ?string => $record->deleted_at?->diffForHumans() ?? '-'),
                ])
                ->columns(3)
                ->visibleOn('edit')
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->defaultImageUrl(fn() => 'https://ui-avatars.com/api/?name=User')
                    ->circular(),

                Tables\Columns\TextColumn::make('employee_code')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jabatan.name')
                    ->label('Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('averageRating')
                    ->label('Rating')
                    ->getStateUsing(fn($record) => number_format($record->averageRating(), 1))
                    ->badge()
                    ->color(fn($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Avg. Rating')
                    ->getStateUsing(fn(Employee $record) => number_format($record->averageRating(), 2))
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 4.0 => 'primary',
                        $state >= 3.0 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray'
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit_id')
                    ->relationship('unit', 'name')
                    ->label('Unit')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('position_id')
                    ->relationship('jabatan', 'name')
                    ->label('Position')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->native(false),

                Tables\Filters\Filter::make('has_user')
                    ->query(fn(Builder $query): Builder => $query->has('user'))
                    ->label('Has User Account'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                // Tables\Actions\Action::make('qrCode')
                //     ->label('QR Code')
                //     ->icon('heroicon-o-qr-code')
                //     ->url(fn($record) => route('filament.admin.resources.employees.qr-code', $record)),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RatingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'qr-code' => Pages\ViewEmployeeQrCode::route('/{record}/qr-code'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
