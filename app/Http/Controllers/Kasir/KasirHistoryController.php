<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PesananLog;

class KasirHistoryController extends Controller
{
    /**
     * List history pesanan (read-only) + filter (portal kasir).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Base Query: Pesanan di cabang kasir ini + Relasi
        $query = \App\Models\Pesanan::with(['creator', 'pelanggan', 'details.layanan'])
            ->where('cabang_id', $user->cabang_id); // Filter Cabang

        // ==========================================
        // 2. LOGIKA SEARCH (Kode & Nama Pelanggan)
        // ==========================================
        if ($kw = trim($request->get('q'))) {
            $query->where(function($q) use ($kw) {
                // A. Cari Kode Pesanan
                $q->where('kode', 'like', "%{$kw}%")
                
                // B. Cari Nama Pelanggan (Masuk ke relasi pelanggan)
                ->orWhereHas('pelanggan', function($sub) use ($kw) {
                    $sub->where('nama_pelanggan', 'like', "%{$kw}%");
                });
            });
        }

        // 3. Filter Tambahan (Status, Bayar, Tanggal) - Tetap dipertahankan
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('paid')) {
            $isPaid = $request->paid == '1' ? 1 : 0;
            $query->where('is_paid', $isPaid);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // 4. Hitung Statistik (Sebelum paginate, gunakan clone query dasar tanpa filter detail)
        // Agar statistik tetap menghitung total cabang, bukan total hasil search
        // Atau bisa juga statistik mengikuti hasil search (opsional). 
        // Di sini saya buat statistik global cabang agar angkanya stabil.
        $statsQuery = \App\Models\Pesanan::where('cabang_id', $user->cabang_id);
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'lunas'       => (clone $statsQuery)->where('is_paid', 1)->count(),
            'belum_lunas' => (clone $statsQuery)->where('is_paid', 0)->count(),
        ];

        // 5. Eksekusi Data
        $orders = $query->latest()
                        ->paginate(10)
                        ->withQueryString();

        return view('role.kasir.history', compact('orders', 'stats'));
    }

    /**
     * Detail untuk modal (opsional).
     */
    /**
     * Detail untuk modal (opsional).
     */
    // File: app/Http/Controllers/Kasir/HistoryController.php

    public function show($id)
    {
        // 1. Ambil Data Pesanan
        $pesanan = Pesanan::with(['pelanggan', 'creator', 'details.layanan', 'cabang'])->findOrFail($id);

        // 2. Ambil Data LOGS (Ini yang sering kelupaan)
        $logs = \App\Models\PesananLog::with('user')
            ->where('pesanan_id', $pesanan->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Format Data untuk JSON (Agar dibaca JavaScript modal)
        return response()->json([
            'order' => [
                'id'         => $pesanan->id,
                'kode'       => $pesanan->kode,
                'customer'   => $pesanan->pelanggan->nama_pelanggan ?? 'Umum',
                'telepon'    => $pesanan->pelanggan->telepon ?? '-',
                'kasir'      => $pesanan->creator->name ?? '-', // Nama Kasir
                'layanan'    => $pesanan->details->first()->layanan->nama ?? '-',
                'berat_kg'   => $pesanan->details->first()->berat_kg ?? 0,
                'total'      => $pesanan->total,
                'status'     => $pesanan->status,
                'is_paid'    => $pesanan->is_paid,
                'created_at' => $pesanan->created_at->format('d M Y H:i'),
            ],
            // Kirim logs ke view
            'logs' => $logs->map(function ($l) {
                return [
                    'by'   => $l->user->name ?? 'Sistem',
                    'from' => $l->from_status,
                    'to'   => $l->to_status,
                    'at'   => $l->created_at->format('d M H:i'),
                    'note' => $l->note
                ];
            })
        ]);
    }

    /**
     * Export CSV sesuai filter aktif.
     */
    public function exportCsv(Request $request)
    {
        $base = Pesanan::query();

        if ($request->filled('status'))    $base->where('status', $request->string('status'));
        if ($request->filled('date_from')) $base->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $base->whereDate('created_at', '<=', $request->date_to);

        // keyword sama seperti index(): hanya kode + customer (kata utuh)
        if (($kw = trim($request->get('q', ''))) !== '') {
            $terms = preg_split('/\s+/', $kw);

            $base->where(function ($outer) use ($terms) {
                foreach ($terms as $t) {
                    $t = trim($t);
                    if ($t === '') continue;

                    $tLower = mb_strtolower($t, 'UTF-8');
                    $customerExpr = "LOWER(CONCAT(' ', TRIM(REPLACE(REPLACE(customer, '  ', ' '), '\t', ' ')), ' '))";

                    $outer->where(function ($q) use ($t, $tLower, $customerExpr) {
                        $q->where('kode', 'like', "%{$t}%")
                          ->orWhereRaw("$customerExpr LIKE ?", ['% ' . $tLower . ' %']);
                    });
                }
            });
        }

        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $base->where('is_paid', (int) $paid);
        }

        $rows = $base->orderByDesc('created_at')->get();

        $filename = 'history_pesanan_kasir_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kode','Customer','Telepon','Layanan','Berat(kg)','Total','Status','Lunas','Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->kode,
                    $r->customer,
                    $r->telepon,
                    $r->layanan,
                    (string) ($r->berat_kg ?? ''),
                    (int) ($r->total ?? 0),
                    $r->status,
                    $r->is_paid ? 'Ya' : 'Belum',
                    optional($r->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}