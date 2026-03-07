<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Extract user data
        $userData = $data['user'] ?? [];
        
        // Ensure name and is_active are synced
        $userData['name'] = $userData['name'] ?? $data['name'];
        $userData['is_active'] = $userData['is_active'] ?? ($data['is_active'] ?? true);
        
        // Extract roles before creating user
        $roles = $userData['roles'] ?? ['karyawan'];
        unset($userData['roles']);

        // Create user
        $user = User::create($userData);

        // Assign roles
        $user->syncRoles($roles);

        // Link user to employee
        $data['user_id'] = $user->id;

        // Remove user data from employee creation
        unset($data['user']);

        return static::getModel()::create($data);
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
