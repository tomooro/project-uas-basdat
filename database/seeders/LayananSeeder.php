<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('layanans')->insert([
            [
                'kode'        => 'LYN001',
                'nama'        => 'Cuci Basah',
                'harga'       => 3000,
                'durasi_jam'  => 48,
                'is_active'   => true,
                'created_by'  => null, // sistem / seeding awal
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'kode'        => 'LYN002',
                'nama'        => 'Cuci Kering Lipat',
                'harga'       => 5000,
                'durasi_jam'  => 72,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'kode'        => 'LYN003',
                'nama'        => 'Cuci Kering Setrika',
                'harga'       => 7000,
                'durasi_jam'  => 72,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'kode'        => 'LYN004',
                'nama'        => 'Setrika',
                'harga'       => 4000,
                'durasi_jam'  => 36,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'kode'        => 'LYN005',
                'nama'        => 'Cuci Basah Express',
                'harga'       => 5000,
                'durasi_jam'  => 5,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],  
            [
                'kode'        => 'LYN006',
                'nama'        => 'Cuci Kering Express',
                'harga'       => 7000,
                'durasi_jam'  => 5,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'kode'        => 'LYN00',
                'nama'        => 'Cuci Kering Express',
                'harga'       => 10000,
                'durasi_jam'  => 6,
                'is_active'   => true,
                'created_by'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],                                 
        ]);
    }
}
