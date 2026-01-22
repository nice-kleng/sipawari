<?php

namespace App\Filament\Resources\RaterResource\Pages;

use App\Filament\Resources\RaterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRater extends ViewRecord
{
    protected static string $resource = RaterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
