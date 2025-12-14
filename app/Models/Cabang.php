<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    // --- BAGIAN INI SANGAT PENTING ---
    // Memberitahu Laravel untuk tidak mencari 'cabangs', tapi 'cabang'
    protected $table = 'cabang'; 
    // ---------------------------------

    protected $fillable = [
        'nama_cabang',
        'alamat',
        'telepon'
    ];
}