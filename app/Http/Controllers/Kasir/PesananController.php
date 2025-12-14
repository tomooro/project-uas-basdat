<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\PesananLog;
use App\Models\Pembayaran;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // <--- TAMBAHKAN BARIS INI WAJIB!
use Illuminate\Support\Facades\Log;  // <--- Tambahkan ini untuk log error

class PesananController extends Controller
{
// app/Http/Controllers/Kasir/PesananController.php

    public function index(Request $request)
    {
        // 1. Query Dasar: Ambil pesanan yang statusnya BUKAN Selesai
        $orders = Pesanan::with(['creator', 'pelanggan', 'details.layanan'])
            ->where('status', '!=', 'Selesai');

        // 2. Filter Cabang (Agar kasir hanya melihat data cabangnya sendiri)
        if (Auth::check()) {
            $orders->where('cabang_id', Auth::user()->cabang_id);
        }

        // 3. LOGIKA SEARCH (Kode / Nama / Telepon)
        if ($kw = trim($request->get('q'))) {
            $orders->where(function ($query) use ($kw) {
                // A. Cari berdasarkan KODE PESANAN (di tabel pesanan)
                $query->where('kode', 'like', '%' . $kw . '%')
                
                // B. Cari berdasarkan NAMA atau TELEPON (di tabel pelanggan)
                      ->orWhereHas('pelanggan', function ($q) use ($kw) {
                          $q->where('nama_pelanggan', 'like', '%' . $kw . '%')
                            ->orWhere('telepon', 'like', '%' . $kw . '%');
                      });
            });
        }

        // 4. Urutkan dari yang terbaru & Paginate
        $orders = $orders->orderBy('created_at', 'desc')
                         ->paginate(12)
                         ->withQueryString();

        return view('role.kasir.pesanan-aktif', compact('orders'));
    }

    public function create(Request $request)
    {
        $layanans = Layanan::where('is_active', true)->orderBy('nama', 'asc')->get(['id','kode','nama','harga','durasi_jam']);
        $view = view('role.kasir.pesanan-baru', compact('layanans'));
        if ($request->boolean('fresh')) {
            return response($view)
                ->header('Cache-Control','no-store,no-cache,must-revalidate,max-age=0')
                ->header('Pragma','no-cache')
                ->header('Expires','0');
        }
        return $view;
    }

    public function store(Request $request)
    {
        // 1. Normalisasi Berat
        if ($request->filled('berat_kg')) {
            $request->merge(['berat_kg' => str_replace(',', '.', $request->input('berat_kg'))]);
        }

        // 2. Validasi
        $data = $request->validate([
            'customer' => ['required','string','max:191'],
            'telepon'  => ['nullable','string','max:50'],
            'layanan'  => ['required','string','max:255', Rule::exists('layanans','nama')->where('is_active',1)],
            'berat_kg' => ['nullable','numeric','min:0'],
            'total'    => ['required','integer','min:0'],
            'catatan'  => ['nullable','string','max:500'],
            'status_pembayaran' => ['required', Rule::in(['lunas','belum_lunas'])],
            'metode_pembayaran' => ['nullable','string','max:50','required_if:status_pembayaran,lunas'],
        ]);


        // 3. Normalisasi No HP
        $normalizedTel = null;
        if (!empty($data['telepon'])) {
            $normalizedTel = $this->normalizeWaNumber($data['telepon']);
        }

        // ============================================================
        // BAGIAN INI YANG KEMARIN SALAH/KURANG
        // ============================================================
// ... (sekitar baris 95 di fungsi store)
// ============================================================
// LOGIKA BARU: SELALU UPDATE NAMA JIKA NOMOR HP SAMA
// ============================================================
        // ... di dalam PesananController.php -> store()
// ... (lanjutkan di dalam fungsi store)

        $pelanggan = null;
        
        if ($normalizedTel) {
            // KODE PERBAIKAN: Cari berdasarkan KOMBINASI HP DAN NAMA.
            // Jika 'Bapak 064' sudah ada, pakai itu. Jika belum, buat baru.
            // Ini memastikan 'Ibu 064' dan 'Bapak 064' adalah entitas berbeda.
            $pelanggan = Pelanggan::firstOrCreate(
                [
                    'telepon' => $normalizedTel, 
                    'nama_pelanggan' => $data['customer']
                ],
                // Data yang akan dibuat jika kombinasi HP+Nama belum ada
                [
                    'kode_pelanggan' => 'PLG-' . strtoupper(Str::random(8))
                ]
            );

        } else {
            // SKENARIO 2: TIDAK ADA HP -> BUAT Pelanggan Baru (tanpa telepon)
            $pelanggan = Pelanggan::create([
                'nama_pelanggan' => $data['customer'],
                'telepon' => null, 
                'kode_pelanggan' => 'PLG-' . strtoupper(Str::random(8))
            ]);
        }

// ... (lanjutkan simpan pesanan dengan $pelanggan->id)
// ...
// ============================================================
// ... (lanjut Simpan Pesanan $order = Pesanan::create([...]) )
        // ============================================================

        $user = auth()->user();
        // 5. Simpan Pesanan (Pastikan pelanggan_id tidak null)
        $order = Pesanan::create([
            'kode' => 'ORD'.strtoupper(Str::random(6)),
            'pelanggan_id' => $pelanggan->id, // INI KUNCINYA (Jangan pakai ?? null)
            'total' => $data['total'],
            'status' => 'Baru',
            'status_pembayaran' => $data['status_pembayaran'],
            'is_paid' => $data['status_pembayaran'] === 'lunas' ? 1 : 0,
            'paid_at' => $data['status_pembayaran'] === 'lunas' ? now() : null,
            'created_by' => Auth::id(),
            'cabang_id' => $user->cabang_id, // <--- AMBIL CABANG_ID KASIR DARI USER LOGIN
        ]);

        // 6. Simpan Detail
        $layananModel = Layanan::where('nama',$data['layanan'])->firstOrFail();
        $order->details()->create([
            'layanan_id' => $layananModel->id,
            'berat_kg'   => $data['berat_kg'],
            'catatan'    => $data['catatan'] ?? null,
            'harga_satuan' => $layananModel->harga,
            'subtotal'      => ($data['berat_kg'] ?? 1) * $layananModel->harga,
        ]);

        // 7. Log
        PesananLog::create([
            'pesanan_id' => $order->id,
            'user_id' => Auth::id(),
            'action' => 'create',
            'to_status' => 'Baru',
            'note' => $order->is_paid ? 'Dibuat (Lunas)' : 'Dibuat (Belum Lunas)',
        ]);

        // 8. Pembayaran
        if ($order->is_paid) {
            Pembayaran::create([
                'pesanan_id' => $order->id,
                'metode' => $request->input('metode_pembayaran') ?? 'tunai',
                'jumlah' => (int)$order->total,
                'user_id' => Auth::id(),
            ]);
        }

        // WA Link
        $waUrl = null;
        if ($normalizedTel) {
            $text = $this->composeWaMessage($order, 'Baru');
            $enc  = urlencode($text);
            $waUrl = "https://wa.me/{$normalizedTel}?text={$enc}";
        }

        return redirect()->route('kasir.pesanan.index')
            ->with('success', 'Pesanan berhasil dibuat.')
            ->with('wa_url', $waUrl); 
    }

    private function normalizeWaNumber(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', $raw ?? '');
        if ($d === '') return null;

        if (str_starts_with($d, '620')) {
            $d = '62' . substr($d, 3);
        } elseif ($d[0] === '0') {
            $d = '62' . substr($d, 1);
        } elseif ($d[0] === '8') {
            $d = '62' . $d;
        }

        if (str_starts_with($d, '62')) {
            $rest = ltrim(substr($d, 2), '0');
            if ($rest === '') return null;
            $d = '62' . $rest;
        }

        $len = strlen($d);
        if ($len < 10 || $len > 15) return null;

        return $d;
    }

    /** Intent (JID + phone) + API khusus nomor tak tersimpan + fallback lengkap */
    private function buildWaTargets(string $phone, string $text, Request $request): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $enc  = rawurlencode($text);

        $jid = $phone . '@s.whatsapp.net';

        $intent_jid   = "intent://send/?jid={$jid}&text={$enc}#Intent;scheme=smsto;package=com.whatsapp;end";
        $intent_phone = "intent://send/?phone={$phone}&text={$enc}#Intent;scheme=smsto;package=com.whatsapp;end";

        $api_std = "https://api.whatsapp.com/send?phone={$phone}&text={$enc}";
        $api_nu  = "https://api.whatsapp.com/send/?phone={$phone}&text={$enc}&type=phone_number&app_absent=0";

        return [
            'app'        => "whatsapp://send?phone={$phone}&text={$enc}",
            'wame'       => "https://wa.me/{$phone}?text={$enc}",
            'api'        => $api_std,
            'api2'       => $api_nu,
            'web'        => "https://web.whatsapp.com/send?phone={$phone}&text={$enc}",
            'intent'     => $intent_phone,
            'intent_jid' => $intent_jid,
            'rawtxt'     => $text,
        ];
    }

    private function bridgeOpenWhatsApp(array $targets, string $backUrl)
    {
        $html = '<!doctype html><html lang="id"><head><meta charset="utf-8">'
              . '<meta name="viewport" content="width=device-width, initial-scale=1">'
              . '<title>Membuka WhatsApp…</title></head><body>'
              . '<script>(function(){'
              . 'var app=' . json_encode($targets['app']) . ';'
              . 'var wame=' . json_encode($targets['wame']) . ';'
              . 'var back=' . json_encode($backUrl) . ';'
              . 'try{window.location.href=app;}catch(e){}'
              . 'setTimeout(function(){try{location.replace(wame);}catch(e){location.href=wame;}},900);'
              . 'document.addEventListener("visibilitychange",function(){if(document.visibilityState==="hidden"){setTimeout(function(){try{location.replace(back);}catch(e){location.href=back;}},1800);}});'
              . 'setTimeout(function(){try{location.replace(back);}catch(e){location.href=back;}},4500);'
              . '})();</script>'
              . '</body></html>';

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function composeWaMessage(Pesanan $p, string $statusBaru): string
    {
        // --- PERBAIKAN DI SINI ---
        // Kita paksa load relasi pelanggan & details untuk memastikan datanya ada
        // (Refresh data dari database)
        $p->loadMissing(['pelanggan', 'details.layanan', 'creator']); 
        
        $kasir     = $p->creator->name ?? 'Kasir';
        $tanggal   = now()->format('d/m/Y - H:i');

        // Ambil nama pelanggan
        // Prioritas 1: Dari relasi database
        // Prioritas 2: String kosong/strip jika gagal
        $customerName = $p->pelanggan->nama_pelanggan ?? '-';

        // Ambil layanan pertama
        $firstDetail = $p->details->first();
        $tipeLayanan = $firstDetail->layanan->nama ?? '-';
        $beratView   = isset($firstDetail->berat_kg) ? number_format((float) $firstDetail->berat_kg, 2, ',', '.') : null;

        $subtotal    = 'Rp. ' . number_format((int) ($p->total ?? 0), 0, ',', '.');
        
        $statusBayar = $p->status_pembayaran
            ? ucfirst($p->status_pembayaran)
            : (($p->is_paid ?? false) ? 'Lunas' : 'Belum Lunas');

        $lines = [
            "LaundryKita",
            "====================",
            "Tanggal : {$tanggal}",
            "No Nota : " . ($p->kode ?? '-'),
            "Kasir : {$kasir}",
            "Nama : " . $customerName, // <--- Ini harusnya sekarang sudah muncul
            "====================",
            "Tipe Layanan : " . $tipeLayanan,
        ];

        if ($beratView) $lines[] = "Berat (kg) = {$beratView}";
        
        $lines[] = "Subtotal = {$subtotal}";
        $lines[] = "Status Pembayaran : {$statusBayar}";
        $lines[] = "====================";
        $lines[] = "Status : {$statusBaru}";
        $lines[] = "====================";
        $lines[] = "Terima kasih telah menggunakan layanan kami.";

        return implode("\n", $lines);
    }


    public function cetakTag($id)
    {
        // 1. Ambil Data
        $pesanan = Pesanan::with(['pelanggan', 'details.layanan', 'creator'])->findOrFail($id);

        // 2. Siapkan Data Manual (Biar gak usah mikir di view)
        $nama_fix = $pesanan->pelanggan ? $pesanan->pelanggan->nama_pelanggan : "Tanpa Nama";
        
        $detail = $pesanan->details->first();
        $layanan_fix = ($detail && $detail->layanan) ? $detail->layanan->nama : "-";

        // 3. PANGGIL FILE YANG BENAR
        // Perhatikan ini: 'role.kasir.tag' (Tanpa .print)
        return view('role.kasir.tag', compact('pesanan', 'nama_fix', 'layanan_fix'));
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. Cari data
        $pesanan = Pesanan::with(['pelanggan', 'creator', 'details.layanan'])->findOrFail($id);

        // /// PERBAIKAN DISINI: AMANKAN STATUS LAMA DULU SEBELUM DIUBAH ///
        $statusLama = $pesanan->status; // Simpan "Baru" ke variabel ini
        // /////////////////////////////////////////////////////////////////

        // 2. Tentukan Status Baru
        $statusBaru = $request->input('status_akhir', 'Siap Ambil');

        // Cek agar tidak update jika status sama (Opsional, biar log ga penuh sampah)
        if ($statusLama === $statusBaru) {
             return redirect()->back(); 
        }

        // 3. Update Database
        $pesanan->status = $statusBaru;
        $pesanan->save(); // Sekarang status di DB berubah jadi "Siap Ambil"

        // 4. Catat Log Perubahan
        PesananLog::create([
            'pesanan_id'  => $pesanan->id,
            'user_id'     => Auth::id(),
            'action'      => 'update_status',
            
            // /// GUNAKAN VARIABEL YANG KITA AMANKAN TADI ///
            'from_status' => $statusLama,  // Ini isinya masih "Baru"
            'to_status'   => $statusBaru,  // Ini isinya "Siap Ambil"
            // ///////////////////////////////////////////////
            
            'note'        => "Status diperbarui menjadi $statusBaru",
        ]);

        // 5. Logic WA (Tetap sama)
        $waUrl = null;
        if ($pesanan->pelanggan && !empty($pesanan->pelanggan->telepon)) {
            $noHp = $this->normalizeWaNumber($pesanan->pelanggan->telepon);
            if ($noHp) {
                $text = $this->composeWaMessage($pesanan, $statusBaru);
                $enc  = urlencode($text);
                $waUrl = "https://wa.me/{$noHp}?text={$enc}";
            }
        }

        $judulAlert = ($statusBaru === 'Selesai') ? 'Pesanan Selesai!' : 'Pesanan Siap Ambil!';

        return redirect()->back()
            ->with('success', "Status berhasil diubah menjadi $statusBaru.")
            ->with('wa_url', $waUrl)
            ->with('alert_title', $judulAlert); 
    }
}