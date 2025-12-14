<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cabang')->insert([
            [
                'nama_cabang' => 'Cabang A',
                'alamat'      => 'Mulyosari',
                'telepon'     => '085853485521',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama_cabang' => 'Cabang B',
                'alamat'      => 'Rungkut',
                'telepon'     => '08125090873',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'nama_cabang' => 'Cabang C',
                'alamat'      => 'Ketintang',
                'telepon'     => '087717394049',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],           
        ]);
    }
}
