<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananDetail extends Model
{
    protected $table = 'pesanan_details'; // optional, ini default Laravel juga
    protected $fillable = ['pesanan_id', 'layanan_id', 'harga_satuan', 'berat_kg', 'subtotal', 'note'];

    /**
     * Casting tipe data.
     */
    protected $casts = [
        'berat_kg' => 'decimal:2',
        'harga'    => 'integer',
        
    ];

    // ===== RELASI =====

    // Detail milik satu pesanan
    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    // Detail terkait satu layanan
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'layanan_id');
    }
}
