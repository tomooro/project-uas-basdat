<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Perintah untuk MENGHAPUS index unique (nama index biasanya adalah nama kolom)
            $table->dropUnique(['telepon']); 
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            // Perintah untuk MENAMBAH index unique kembali (jika rollback)
            $table->unique('telepon');
        });
    }
};