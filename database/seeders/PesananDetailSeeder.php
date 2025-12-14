<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PesananDetailSeeder extends Seeder
{
    public function run(): void
    {
        $pesananIds = DB::table('pesanan')->pluck('id');
        $layanans   = DB::table('layanans')->get();

        foreach ($pesananIds as $pesananId) {

            // 1–3 layanan per pesanan
            $jumlahLayanan = rand(1, 3);
            $layanansDipilih = $layanans->random($jumlahLayanan);

            foreach ($layanansDipilih as $layanan) {

                $beratKg = rand(1, 10); // 1–10 kg
                $subtotal = $beratKg * $layanan->harga;

                DB::table('pesanan_details')->insert([
                    'pesanan_id'   => $pesananId,
                    'layanan_id'   => $layanan->id,
                    'harga_satuan' => $layanan->harga,
                    'berat_kg'     => $beratKg,
                    'subtotal'     => $subtotal,
                    'note'         => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
