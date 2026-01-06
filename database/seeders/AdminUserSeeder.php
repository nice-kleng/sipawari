<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $admin->assignRole('super_admin');

        $this->command->info('Super Admin created successfully!');
        $this->command->info('Email: admin@hospital.com');
        $this->command->info('Password: password');
    }
}
