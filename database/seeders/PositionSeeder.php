<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'kode' => 'DOKTER',
                'name' => 'Dokter',
            ],
            [
                'kode' => 'PERAWAT',
                'name' => 'Perawat',
            ],
            [
                'kode' => 'BIDAN',
                'name' => 'Bidan',
            ],
            [
                'kode' => 'FARMASIS',
                'name' => 'Farmasis',
            ],
            [
                'kode' => 'RADIOGRAFER',
                'name' => 'Radiografer',
            ],
            [
                'kode' => 'ANALIS-LAB',
                'name' => 'Analis Laboratorium',
            ],
            [
                'kode' => 'ADMIN',
                'name' => 'Administrator',
            ],
        ];

        foreach ($positions as $position) {
            \App\Models\Position::create($position);
        }
    }
}
