<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\KasirPasswordMail;
use Illuminate\Support\Facades\Mail;


class KaryawanController extends Controller
{
    // 1. DAFTAR KARYAWAN
    public function index()
    {
        // Ambil user role kasir + data cabang relasinya
        $kasir = User::where('role', 'kasir')
            ->with('cabang') // Load relasi cabang agar tidak N+1 Query
            ->orderBy('name')
            ->get();

        // Ambil semua cabang untuk dropdown di Modal Tambah/Edit
        $cabangs = Cabang::all();

        // Pastikan nama view sesuai folder: resources/views/role/pemilik/karyawan.blade.php
        return view('role.pemilik.karyawan', [
            'karyawans'   => $kasir,     // Saya ubah jadi variable $karyawans biar enak dibaca di view
            'cabangs'     => $cabangs,   // Data cabang dikirim ke view
        ]);
    }

    // 2. SIMPAN KARYAWAN BARU
    public function store(Request $r)
    {
        // Validasi
        $data = $r->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'nullable|min:6',
            'phone'     => 'nullable|string|max:25',
            'cabang_id' => 'required|exists:cabang,id',
        ]);

        // Generate password jika kosong
        $tempPassword = $data['password'] ?? Str::random(8);

        // Simpan user
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $tempPassword, // akan di-hash oleh mutator
            'phone'     => $data['phone'] ?? null,
            'role'      => 'kasir',
            'cabang_id' => $data['cabang_id'],
        ]);

        // 🔥 KIRIM EMAIL KE KASIR
        try {
            Mail::to($user->email)->send(
                new KasirPasswordMail($user, $tempPassword)
            );
        } catch (\Exception $e) {
            // kalau email gagal, user tetap tersimpan
            return back()->with([
                'success'    => 'Karyawan berhasil ditambahkan, tetapi email gagal dikirim.',
                'temp_email' => $user->email,
                'temp_pass'  => $tempPassword,
            ]);
        }

        // Sukses
        return back()->with([
            'success'    => 'Karyawan baru berhasil ditambahkan & email login telah dikirim.',
            'temp_email' => $user->email,
            'temp_pass'  => $tempPassword,
        ]);
    }


    // 3. UPDATE KARYAWAN
    public function update(Request $r, $id) // Pakai $id biar lebih fleksibel
    {
        $user = User::findOrFail($id);

        $data = $r->validate([
            'name'      => 'required|string|max:100',
            // Validasi email unik kecuali punya user ini sendiri
            'email'     => 'required|email|unique:users,email,'.$id,
            'password'  => 'nullable|min:6',
            'phone'     => 'nullable|string|max:25',
            'cabang_id' => 'required|exists:cabang,id',
        ]);

        // Update data
        $user->name      = $data['name'];
        $user->email     = $data['email'];
        $user->phone     = $data['phone'] ?? $user->phone;
        $user->cabang_id = $data['cabang_id']; // Update Cabang

        // Update password hanya jika diisi
        if (!empty($data['password'])) {
            $user->password = $data['password']; 
        }

        $user->save();

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    // 4. HAPUS KARYAWAN
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Cek agar tidak sengaja menghapus sesama Owner atau Admin
        if($user->role !== 'kasir') {
            return back()->withErrors(['Gagal' => 'Hanya akun kasir yang boleh dihapus dari sini.']);
        }

        $user->delete();
        return back()->with('success', 'Karyawan berhasil dihapus.');
    }
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Pastikan yang direset hanya kasir (safety)
        if($user->role !== 'kasir') {
            return back()->withErrors(['Gagal' => 'Hanya akun kasir yang boleh direset.']);
        }

        // Generate password acak 6 karakter
        $newPassword = Str::random(6); 

        // Update password (Mutator di Model User akan otomatis meng-hash ini)
        $user->password = $newPassword;
        $user->save();

        // Kembalikan ke halaman dengan info password baru
        return back()->with([
            'success'    => 'Password berhasil direset.',
            'temp_email' => $user->email,
            'temp_pass'  => $newPassword, // Tampilkan password asli agar bisa dicatat owner
        ]);
    }
}