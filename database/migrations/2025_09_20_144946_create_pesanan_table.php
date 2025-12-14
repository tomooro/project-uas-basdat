<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();

            $table->string('kode', 50)->unique();

            // FK pelanggan
            $table->unsignedBigInteger('pelanggan_id')->nullable();

            // total harga
            $table->unsignedBigInteger('total')->default(0);

            // status pembayaran
            $table->enum('status_pembayaran', ['belum_lunas', 'lunas'])
                  ->default('belum_lunas');

            // waktu lunas
            $table->timestamp('paid_at')->nullable();

            // status pesanan
            $table->enum('status', ['Baru','Siap Ambil','Selesai'])
                  ->default('Baru');

            // flag lama
            $table->boolean('is_paid')->default(false);

            // user pembuat
            $table->unsignedBigInteger('created_by')->nullable();

            // FK ke cabang
            $table->unsignedBigInteger('cabang_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
