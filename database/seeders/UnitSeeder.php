<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'kode' => 'POLI-UMUM',
                'name' => 'Poliklinik Umum'
            ],
            [
                'kode' => 'RUANG-RI',
                'name' => 'Ruang Rawat Inap',
            ],
            [
                'kode' => 'IGD',
                'name' => 'IGD',
            ],
            [
                'kode' => 'LBO',
                'name' => 'Laboartorium',
            ],
            [
                'kode' => 'RAD',
                'name' => 'Radiologi',
            ],
            [
                'kode' => 'FAR',
                'name' => 'Farmasi',
            ],
            [
                'kode' => 'ADM',
                'name' => 'Administrasi',
            ],
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::create($unit);
        }
    }
}
