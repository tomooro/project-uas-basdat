<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan FK cabang_id ke tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cabang_id')) {
                $table->unsignedBigInteger('cabang_id')->nullable()->index();
            }
            $table->foreign('cabang_id')
                  ->references('id')->on('cabang')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });

        // Tambahkan FK cabang_id ke tabel pesanan
        Schema::table('pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan', 'cabang_id')) {
                $table->unsignedBigInteger('cabang_id')->nullable()->index();
            }
            $table->foreign('cabang_id')
                  ->references('id')->on('cabang')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        // Drop FK di users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
        });

        // Drop FK di pesanan
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
        });
    }
};
