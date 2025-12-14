<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahkan ini standar Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pelanggan extends Model
{
    use HasFactory;

    // 1. Nama Tabel (Sesuai DB)
    protected $table = 'pelanggan';

    // 2. Primary Key (Sesuai DB)
    protected $primaryKey = 'id';

    // 3. Timestamps (Sesuai DB: ada created_at & updated_at, jadi harus TRUE)
    public $timestamps = true;

    // 4. Fillable (WAJIB SAMA DENGAN NAMA KOLOM DI DATABASE)
    // Perhatikan saya ubah 'telp_pelanggan' jadi 'telepon'
    protected $fillable = [
        'kode_pelanggan',
        'nama_pelanggan',
        'telepon',        // <--- INI KUNCINYA (Dulu salah tulis telp_pelanggan)
        
        // Kolom di bawah ini opsional (karena di 'desc pelanggan' belum ada), 
        // tapi biarkan saja tidak apa-apa, asal kolom 'telepon' sudah benar.
        'email',
        'tanggal_lahir',
        'password',
    ];

    // Menyembunyikan password saat di-return sebagai JSON
    protected $hidden = ['password'];

    // Auto-generate kode pelanggan unik saat record dibuat
    protected static function booted()
    {
        static::creating(function (self $pel) {
            if (empty($pel->kode_pelanggan)) {
                // Generate kode unik, misal: PLG-A1B2C3D4
                $pel->kode_pelanggan = 'PLG-' . strtoupper(Str::random(8));
            }
        });
    }

    // Hash otomatis password
    public function setPasswordAttribute($value): void
    {
        if (is_null($value) || $value === '') {
            $this->attributes['password'] = null;
            return;
        }
        $this->attributes['password'] = Str::startsWith($value, '$2y$')
            ? $value
            : bcrypt($value);
    }

    // Relasi: satu pelanggan punya banyak pesanan
    public function pesanans()
    {
        // Parameter: (Model Tujuan, Foreign Key di sana, Local Key di sini)
        // Local Key harus 'id', bukan 'ID_pelanggan'
        return $this->hasMany(Pesanan::class, 'pelanggan_id', 'id');
    }
}