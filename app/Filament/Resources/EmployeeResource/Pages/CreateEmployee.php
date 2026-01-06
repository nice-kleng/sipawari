<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create user first
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['user']['email'],
            'password' => Hash::make($data['user']['password']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Assign employee role
        $user->assignRole('karyawan');

        // Add user_id to employee data
        $data['user_id'] = $user->id;

        // Remove user array as it's already processed
        unset($data['user']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Karyawan berhasil ditambahkan';
    }
}
