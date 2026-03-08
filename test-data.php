<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Models\User;
use App\Models\Unit;
use App\Models\Jabatan;
use App\Models\Position;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Persiapan roles dan permissions untuk test
$managerRole = Role::firstOrCreate(['name' => 'kepala_bagian']);
$managerRole->givePermissionTo('view_team_dashboard');

$staffRole = Role::firstOrCreate(['name' => 'staf']);

$unit = Unit::firstOrCreate(['name' => 'IT Department'], ['kode' => 'IT-001', 'description' => 'IT Department']);
$jabatanManager = Position::firstOrCreate(['name' => 'Manajer Sistem'], ['description' => 'Manajer Sistem']);
$jabatanStaff = Position::firstOrCreate(['name' => 'Staff Support'], ['description' => 'Staff Support']);

try {
    // 1. Buat data dummy manager (jika belum ada)
    $managerUser = User::firstOrCreate(['email' => 'manager_hr@rs.com'], ['name' => 'Manager HR', 'password' => bcrypt('password')]);
    
    $manager = Employee::updateOrCreate(
        ['employee_code' => 'MGR-001'],
        [
            'name' => 'Manager HR',
            'email' => 'manager_hr@rs.com',
            'unit_id' => $unit->id,
            'jabatan_id' => $jabatanManager->id,
            'status' => 'Pegawai Tetap',
            'is_active' => true,
        ]
    );

    $managerUser->employee()->save($manager);
    $managerUser->assignRole('kepala_bagian');

    // 2. Buat data dummy staff
    $staffUser = User::firstOrCreate(['email' => 'staff_hr@rs.com'], ['name' => 'Staff HR', 'password' => bcrypt('password')]);

    $staff = Employee::updateOrCreate(
        ['employee_code' => 'STF-001'],
        [
            'name' => 'Staff HR',
            'email' => 'staff_hr@rs.com',
            'unit_id' => $unit->id,
            'jabatan_id' => $jabatanStaff->id,
            'status' => 'Pegawai Kontrak',
            'is_active' => true,
            'manager_id' => $manager->id,
        ]
    );

    $staffUser->employee()->save($staff);
    $staffUser->assignRole('staf');

    echo "Dummy data created successfully.\n";
    echo "Manager: manager_hr@rs.com (password: password)\n";
    echo "Staff: staff_hr@rs.com (password: password)\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
