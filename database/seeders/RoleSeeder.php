<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::create(['name' => 'super_admin']);
        $karyawan = Role::create(['name' => 'karyawan']);

        // Define permissions for employees
        $employeePermissions = [
            'view_employee',
            'view_any_employee',
        ];

        // Define permissions for ratings
        $ratingPermissions = [
            'view_rating',
            'view_any_rating',
        ];

        // Create all permissions
        foreach (array_merge($employeePermissions, $ratingPermissions) as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to karyawan role
        // $karyawan->givePermissionTo([
        //     'view_employee',
        //     'view_rating',
        // ]);

        // Super admin gets all permissions automatically via Shield
    }
}
