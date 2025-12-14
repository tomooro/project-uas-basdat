<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pesanan_id')
                  ->constrained('pesanan')  // tabel tujuan
                  ->onDelete('cascade');    // kalau pesanan dihapus → detail ikut hilang

            $table->foreignId('layanan_id')
                  ->constrained('layanans')
                  ->onDelete('restrict');   // tidak boleh hapus layanan yang dipakai

            $table->unsignedInteger('harga_satuan');
            $table->decimal('berat_kg', 8, 2);
            $table->unsignedInteger('subtotal');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_details');
    }
};
