<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {

            DB::table('pelanggan')->insert([
                'kode_pelanggan' => 'PLG-' . strtoupper(Str::random(8)),
                'nama_pelanggan' => 'Pelanggan ' . $i,
                'telepon'        => '08' . rand(111111111, 999999999),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

        }
    }
}
