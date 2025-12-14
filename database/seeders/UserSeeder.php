<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID cabang berdasarkan nama
        $cabangA = DB::table('cabang')->where('nama_cabang', 'Cabang A')->value('id');
        $cabangB = DB::table('cabang')->where('nama_cabang', 'Cabang B')->value('id');
        $cabangC = DB::table('cabang')->where('nama_cabang', 'Cabang C')->value('id');

        DB::table('users')->insert([
            [
                'name'       => 'Kasir 1 Cabang A',
                'email'      => 'nabellayunita2004@gmail.com',
                'phone'      => '085853485521',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'cabang_id'  => $cabangA,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name'       => 'Kasir 1 Cabang B',
                'email'      => 'dhiyaz@gmail.com',
                'phone'      => '08125090873',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'cabang_id'  => $cabangB,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'name'       => 'Kasir 1 Cabang C',
                'email'      => 'naramediandra@gmail.com',
                'phone'      => '087717394049',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'cabang_id'  => $cabangC,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ]);
    }
}
