<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sinkronkan is_paid -> status_pembayaran + paid_at
        DB::table('pesanan')
            ->where('is_paid', 1)
            ->update([
                'status_pembayaran' => 'lunas',
                'paid_at' => DB::raw('COALESCE(paid_at, NOW())'),
            ]);

        DB::table('pesanan')
            ->where(function ($q) {
                $q->whereNull('is_paid')->orWhere('is_paid', 0);
            })
            ->update(['status_pembayaran' => 'belum_lunas']);
    }

    public function down(): void
    {
        // tidak ada rollback
    }
};
