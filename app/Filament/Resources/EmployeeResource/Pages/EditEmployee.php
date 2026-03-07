<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->user) {
            $data['user'] = $this->record->user->toArray();
            $data['user']['roles'] = $this->record->user->roles->pluck('id')->toArray();
        }

        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Extract user data
        $userData = $data['user'] ?? [];
        
        // Ensure name and is_active are synced
        $userData['name'] = $userData['name'] ?? $data['name'];
        $userData['is_active'] = $userData['is_active'] ?? ($data['is_active'] ?? true);
        
        // Extract roles
        $roles = $userData['roles'] ?? [];
        unset($userData['roles']);

        // Update user
        if ($record->user) {
            $record->user->update($userData);
            if (!empty($roles)) {
                $record->user->syncRoles($roles);
            }
        }

        // Remove user data from employee update
        unset($data['user']);

        $record->update($data);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
