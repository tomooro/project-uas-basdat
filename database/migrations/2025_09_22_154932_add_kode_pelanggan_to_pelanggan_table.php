<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan kolom hanya jika belum ada
        if (!Schema::hasColumn('pelanggan', 'kode_pelanggan')) {

            Schema::table('pelanggan', function (Blueprint $table) {
                $table->string('kode_pelanggan', 20)->nullable()->after('id');
            });

            // 2. Tambahkan unique index setelah kolom ada
            Schema::table('pelanggan', function (Blueprint $table) {
                $table->unique('kode_pelanggan');
            });
        }

        // 3. Backfill only if column exists
        $rows = DB::table('pelanggan')->select('id', 'kode_pelanggan')->get();

        foreach ($rows as $r) {
            if (!$r->kode_pelanggan) {
                $kode = 'PLG-' . strtoupper(Str::random(8));

                // pastikan unik
                while (DB::table('pelanggan')->where('kode_pelanggan', $kode)->exists()) {
                    $kode = 'PLG-' . strtoupper(Str::random(8));
                }

                DB::table('pelanggan')
                    ->where('id', $r->id)
                    ->update(['kode_pelanggan' => $kode]);
            }
        }
    }

    public function down(): void
    {
        // Pastikan kolom ada sebelum dihapus
        if (Schema::hasColumn('pelanggan', 'kode_pelanggan')) {

            // Nama index unik default Laravel: namaKolom_unique
            Schema::table('pelanggan', function (Blueprint $table) {
                $table->dropUnique('pelanggan_kode_pelanggan_unique');
            });

            Schema::table('pelanggan', function (Blueprint $table) {
                $table->dropColumn('kode_pelanggan');
            });
        }
    }
};
