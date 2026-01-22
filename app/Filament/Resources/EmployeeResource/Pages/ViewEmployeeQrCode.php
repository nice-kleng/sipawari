<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\Employee;
use Filament\Resources\Pages\Page;

class ViewEmployeeQrCode extends Page
{
    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.resources.employee-resource.pages.view-employee-qr-code';

    public ?Employee $record = null;

    public function mount(Employee $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'QR Code - ' . $this->record->name;
    }
}
