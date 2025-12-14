<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cabang; // Pastikan Model Cabang sudah ada
use Illuminate\Support\Str;

class CabangController extends Controller
{
    // 1. Menampilkan daftar cabang
    public function index()
    {
        $cabangs = Cabang::orderBy('id', 'asc')->get();
        return view('role.pemilik.cabang', compact('cabangs'));
    }

    // 2. Menyimpan cabang baru
    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_cabang' => 'required|string|max:100|unique:cabang,nama_cabang',
            'alamat'      => 'nullable|string|max:255',
            'telepon'     => 'nullable|string|max:30',
        ]);
        
        Cabang::create($data);
        
        return back()->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    // 3. Mengupdate cabang
    public function update(Request $r, $id)
    {
        $cabang = Cabang::findOrFail($id);

        $data = $r->validate([
            // Logika unique/except ID sudah benar
            'nama_cabang' => 'required|string|max:100|unique:cabang,nama_cabang,' . $id,
            'alamat'      => 'nullable|string|max:255',
            'telepon'     => 'nullable|string|max:30',
        ]);

        $cabang->update($data);
        return back()->with('success', 'Data cabang berhasil diperbarui.');
    }
    
    // 4. Menghapus cabang
    public function destroy($id)
    {
        $cabang = Cabang::findOrFail($id);
        
        // Hapus: FK di users/pesanans harusnya sudah ditangani oleh onDelete (SET NULL atau CASCADE)
        $cabang->delete(); 
        
        return back()->with('success', 'Cabang berhasil dihapus.');
    }
}