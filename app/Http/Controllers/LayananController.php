<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\LayananLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LayananController extends Controller
{
    // Tampilkan daftar layanan
    public function index()
    {
        $items     = Layanan::orderBy('created_at', 'asc')->get();
        $total     = $items->count();
        $avgPrice  = $total ? (int) round($items->avg('harga')) : 0;
        $avgDurasi = $total ? (int) round($items->avg('durasi_jam')) : 0;

        return view('role.pemilik.layanan', compact('items','total','avgPrice','avgDurasi'));
    }

    // Tambah layanan baru
// ...
// Tambah layanan baru
    public function store(Request $r)
    {
        $data = $r->validate([
            'nama'       => 'required|string|max:100',
            'harga'      => 'required|integer|min:0',
            'durasi_jam' => 'required|integer|min:1',
        ]);

        // --- LOGIKA PENGECEKAN DUPLIKASI (CASE-INSENSITIVE) ---
        $exists = Layanan::whereRaw('LOWER(nama) = ?', [mb_strtolower($data['nama'], 'UTF-8')])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->withErrors([
                'nama' => 'Nama layanan ini sudah ada'
            ]);
        }
        // ----------------------------------------------------

        // Generate kode otomatis: LYN001, LYN002, ...
        $last = Layanan::where('kode','like','LYN%')->orderBy('kode','desc')->first();
        // ... (kode lainnya tetap)
        $num  = $last ? (int) Str::after($last->kode,'LYN') + 1 : 1;

        $user = auth()->user();

        // Simpan layanan
        $layanan = Layanan::create([
            'kode'       => 'LYN'.str_pad($num, 3, '0', STR_PAD_LEFT),
            'nama'       => $data['nama'],
            'harga'      => $data['harga'],
            'durasi_jam' => $data['durasi_jam'],
            'is_active'  => true,
            'created_by' => $user?->id,
        ]);

        // Simpan log CREATE
        LayananLog::create([
            'layanan_id' => $layanan->id,
            'action'     => 'create',
            'nama'       => $layanan->nama,
            'harga'      => $layanan->harga,
            'durasi_jam' => $layanan->durasi_jam,
            'is_active'  => $layanan->is_active,
            'user_id'    => $user?->id,
        ]);

        return back()->with('ok','Layanan baru berhasil ditambahkan.');
    }

    // Update layanan
// ...
// Update layanan
    public function update(Request $r, $id)
    {
        $svc = Layanan::findOrFail($id);

        $data = $r->validate([
            'nama'       => 'required|string|max:100',
            'harga'      => 'required|integer|min:0',
            'durasi_jam' => 'required|integer|min:1',
            'is_active'  => 'nullable|boolean',
        ]);

        // --- LOGIKA PENGECEKAN DUPLIKASI (CASE-INSENSITIVE) SAAT UPDATE ---
        $exists = Layanan::whereRaw('LOWER(nama) = ?', [mb_strtolower($data['nama'], 'UTF-8')])
            ->where('id', '!=', $svc->id) // <--- PENTING: Abaikan layanan saat ini
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->withErrors([
                'nama' => 'Nama layanan ini sudah digunakan oleh layanan lain.'
            ]);
        }
        // -------------------------------------------------------------------

        $svc->update([
            'nama'       => $data['nama'],
            'harga'      => $data['harga'],
            'durasi_jam' => $data['durasi_jam'],
            'is_active'  => $r->boolean('is_active', true),
        ]);

        // Log UPDATE
        $user = auth()->user();
        LayananLog::create([
            'layanan_id' => $svc->id,
            'action'     => 'update',
            'nama'       => $svc->nama,
            'harga'      => $svc->harga,
            'durasi_jam' => $svc->durasi_jam,
            'is_active'  => $svc->is_active,
            'user_id'    => $user?->id,
        ]);

        return back()->with('ok','Layanan berhasil diperbarui.');
    }

    // Hapus layanan
    public function destroy($id)
    {
        $svc = Layanan::findOrFail($id);

        // Log DELETE
        $user = auth()->user();
        LayananLog::create([
            'layanan_id' => $svc->id,
            'action'     => 'delete',
            'nama'       => $svc->nama,
            'harga'      => $svc->harga,
            'durasi_jam' => $svc->durasi_jam,
            'is_active'  => $svc->is_active,
            'user_id'    => $user?->id,
        ]);

        $svc->delete();
        return back()->with('ok','Layanan dihapus.');
    }

    // Tampilkan history log layanan
    public function history()
    {
        $logs = LayananLog::with(['layanan','user'])
            ->latest()
            ->paginate(15);

        return view('role.pemilik.layanan_history', compact('logs'));
    }
}
