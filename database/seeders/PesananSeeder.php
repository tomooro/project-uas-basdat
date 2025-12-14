<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PesananSeeder extends Seeder
{
    public function run(): void
    {
        $pelangganIds = DB::table('pelanggan')->pluck('id');
        $cabangIds    = DB::table('cabang')->pluck('id');

        foreach (range(1, 2500) as $i) {

            // pilih cabang dulu
            $cabangId = $cabangIds->random();

            // pilih kasir di cabang tsb
            $userId = DB::table('users')
                ->where('cabang_id', $cabangId)
                ->whereIn('role', ['kasir','pemilik'])
                ->inRandomOrder()
                ->value('id');

            // fallback (kalau belum ada kasir)
            if (!$userId) {
                $userId = DB::table('users')->inRandomOrder()->value('id');
            }

            $createdAt = Carbon::now()->subDays(rand(1, 365));
            $isPaid    = rand(0,1);

            DB::table('pesanan')->insert([
                'kode'               => 'PSN-' . strtoupper(Str::random(8)),
                'pelanggan_id'       => $pelangganIds->random(),
                'total'              => 0, // akan dihitung dari detail
                'status_pembayaran'  => $isPaid ? 'lunas' : 'belum_lunas',
                'paid_at'            => $isPaid ? $createdAt->copy()->addHours(rand(1,24)) : null,
                'status'             => collect(['Baru','Siap Ambil','Selesai'])->random(),
                'is_paid'            => $isPaid,
                'created_by'         => $userId,        
                'cabang_id'          => $cabangId,
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ]);
        }
    }
}
