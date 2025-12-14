<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Pesanan extends Model
{
    // Nama tabel sesuai DB
    protected $table = 'pesanan';

    /**
     * Kolom yang bisa di-mass assign.
     * Hanya sesuai kolom yang ada di database.
     */
    protected $fillable = [
        'kode',
        'pelanggan_id',
        'total',
        'status',
        'status_pembayaran', // 'lunas' | 'belum_lunas'
        'is_paid',           // boolean
        'paid_at',           // timestamp saat lunas
        'created_by',
        'cabang_id',         // optional, kalau pakai cabang
    ];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'is_paid'  => 'boolean',
        'paid_at'  => 'datetime',
        'total'    => 'integer',
    ];

    // ===== RELASI =====

    // Banyak pembayaran untuk satu pesanan
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    // Pesanan milik satu pelanggan
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    // User/pegawai yang membuat pesanan
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Log perubahan/status pesanan
    public function logs()
    {
        return $this->hasMany(PesananLog::class, 'pesanan_id');
    }

    // Detail pesanan (layanan, berat, catatan, harga)
    public function details()
    {
        return $this->hasMany(PesananDetail::class, 'pesanan_id');
    }
    public function getTotalBeratAttribute()
    {
        // Ini akan menjumlahkan kolom 'berat_kg' dari tabel pesanan_details
        return $this->details->sum('berat_kg');
    }

    /**
     * Accessor sederhana: $pesanan->kasir_name
     * Mengembalikan nama user pembuat pesanan (jika ada).
     */
    public function getKasirNameAttribute(): ?string
    {
        return $this->creator->name ?? null;
    }
    // Tambahkan Accessor ini:
    // Accessor untuk mendapatkan TOTAL BERAT dari semua detail pesanan
    public function getBeratKgAttribute(): float
    {
        // Pastikan relasi details sudah di-load agar tidak N+1 query
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        
        // Hitung total berat dari semua detail
        return (float) $this->details->sum('berat_kg');
    }

    // Tambahkan Accessor ini:
    // Accessor untuk mendapatkan NAMA LAYANAN PERTAMA
    // app/Models/Pesanan.php
// ...

// Accessor untuk mendapatkan TOTAL dari semua detail pesanan
    public function getTotalAttribute(): int
    {
        // Memuat relasi details jika belum dimuat
        if (!$this->relationLoaded('details')) {
            $this->load('details');
        }
        
        // Jumlahkan semua kolom subtotal dari details
        return (int) $this->details->sum('subtotal');
    }

    // ...
        // ... (kode lainnya)
// app/Models/Pesanan.php
    protected static function booted()
    {
        static::addGlobalScope('byUserCabang', function (Builder $builder) {
            $user = auth()->user();

            // Scope hanya berlaku jika user login BUKAN Owner/Admin, DAN punya cabang_id
            if ($user && $user->role === 'kasir' && $user->cabang_id) {
                // Semua query Pesanan OTOMATIS difilter ke cabang Kasir tsb
                $builder->where('cabang_id', $user->cabang_id);
            }
        });
    }
// ...
    public function cabang()
    {
        return $this->belongsTo(\App\Models\Cabang::class, 'cabang_id');
    }
    // ...

}
