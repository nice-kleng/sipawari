<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

                    Forms\Components\TextInput::make('position.name')
                        ->label('Jabatan')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('unit.name')
                        ->label('Departemen / Unit')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->required(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),

                Tables\Columns\TextColumn::make('employee_code')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('department')
                    ->label('Departemen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('averageRating')
                    ->label('Rating')
                    ->getStateUsing(fn($record) => number_format($record->averageRating(), 1))
                    ->badge()
                    ->color(fn($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('totalRatings')
                    ->label('Total Penilaian')
                    ->getStateUsing(fn($record) => $record->totalRatings())
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('unit')
                    ->label('Unit')
                    ->relationship('unit', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\Action::make('qrCode')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn($record) => route('filament.admin.resources.employees.qr-code', $record))
                    ->openUrlInNewTab(),

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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
            'qr-code' => Pages\ViewEmployeeQrCode::route('/{record}/qr-code'),
        ];
    }
}
