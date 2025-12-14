<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $pesanans = DB::table('pesanan')->get();

        // Ambil user (karyawan)
        $users = DB::table('users')->pluck('id');

        foreach ($pesanans as $psn) {

            // Jika belum lunas, lewati (tidak ada pembayaran)
            if ($psn->status_pembayaran !== 'lunas') {
                continue;
            }

            DB::table('pembayarans')->insert([
                'pesanan_id' => $psn->id,
                'metode'     => collect(['cash', 'transfer', 'qris'])->random(),
                'jumlah'     => $psn->total, // ✅ kolom yang BENAR
                'user_id'    => $users->random(),
                'created_at' => Carbon::parse($psn->created_at)->addHours(rand(1, 24)),
                'updated_at' => now(),
            ]);
        }
    }
}
