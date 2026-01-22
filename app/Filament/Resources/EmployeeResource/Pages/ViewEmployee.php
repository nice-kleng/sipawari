<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Storage;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('download_qr')
                ->label('Download QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('info')
                ->url(fn() => Storage::url($this->record->qr_code_path))
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->qr_code_path),
            Actions\Action::make('regenerate_qr')
                ->label('Regenerate QR')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->generateQrCode();
                    $this->notify('success', 'QR Code regenerated successfully');
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('photo')
                            ->circular()
                            ->defaultImageUrl('https://ui-avatars.com/api/?name=User')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('employee_code')
                            ->label('Employee Code')
                            ->copyable()
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('name')
                            ->label('Full Name')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-m-envelope')
                            ->default('No user account'),

                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])->columns(2),

                Infolists\Components\Section::make('Work Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('unit.name')
                            ->label('Unit')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('jabatan.name')
                            ->label('Position')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('jabatan.description')
                            ->label('Position Description')
                            ->default('No description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('QR Code & Rating URL')
                    ->schema([
                        Infolists\Components\TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('rating_url')
                            ->label('Public Rating URL')
                            ->copyable()
                            ->url(fn($record) => $record->rating_url)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),

                        Infolists\Components\ImageEntry::make('qr_code_path')
                            ->label('QR Code')
                            ->disk('public')
                            ->size(200)
                            ->columnSpanFull(),
                    ])->columns(1),

                Infolists\Components\Section::make('Performance Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_ratings')
                            ->label('Total Ratings')
                            ->getStateUsing(fn($record) => $record->totalRatings())
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('average_rating')
                            ->label('Average Rating')
                            ->getStateUsing(fn($record) => number_format($record->averageRating(), 2))
                            ->badge()
                            ->color(fn($state) => match (true) {
                                $state >= 4.5 => 'success',
                                $state >= 4.0 => 'primary',
                                $state >= 3.0 => 'warning',
                                default => 'danger'
                            }),

                        Infolists\Components\TextEntry::make('ratings_this_month')
                            ->label('Ratings This Month')
                            ->getStateUsing(fn($record) => $record->ratingsThisMonth())
                            ->badge()
                            ->color('info'),
                    ])->columns(3),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d M Y H:i:s'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('d M Y H:i:s'),

                        Infolists\Components\TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('d M Y H:i:s'),
                    ])->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
