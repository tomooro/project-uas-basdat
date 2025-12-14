<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel master pelanggan (tanpa timestamps)
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id(); // id PK auto_increment (sesuai acuan)
            $table->string('kode_pelanggan', 20)->nullable()->unique();
            $table->string('nama_pelanggan', 150);
            $table->string('telepon', 30)->nullable()->unique();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });


        // (opsional, tapi disarankan) tambahkan FK ke tabel pesanan
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                if (!Schema::hasColumn('pesanan', 'pelanggan_id')) {
                    $table->unsignedBigInteger('pelanggan_id')->nullable();
                }
                $table->foreign('pelanggan_id')
                    ->references('id')->on('pelanggan')
                    ->nullOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pesanan')) {
            Schema::table('pesanan', function (Blueprint $table) {
                // jika FK pernah dibuat, lepaskan dulu
                if (Schema::hasColumn('pesanan', 'pelanggan_id')) {
                    $table->dropForeign(['pelanggan_id']);
                }
            });
        }

        Schema::dropIfExists('pelanggan');
    }
};
