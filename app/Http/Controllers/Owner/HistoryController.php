<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\PesananLog;
use App\Models\Cabang;

class HistoryController extends Controller
{
    /**
     * List history pesanan (read-only) + filter.
     */
    public function index(Request $request)
    {
        // 1) Base query: Load relasi 'creator' dan cabang milik creator tersebut
        // Kita tidak load 'cabang' langsung karena pesanan tidak punya kolom cabang_id
        $base = Pesanan::query()->with(['creator.cabang', 'pelanggan']);

        // Ambil semua daftar cabang untuk DROPDOWN FILTER
        $cabangs = \App\Models\Cabang::all();
        $kasirs  = \App\Models\User::where('role', 'kasir')->get();
        if ($request->filled('kasir_id')) {
                $base->where('created_by', $request->kasir_id);
        }
        // --- FILTER CABANG (VIA RELASI CREATOR/KASIR) ---
        if ($request->filled('cabang_id')) {
            $base->whereHas('creator', function($q) use ($request) {
                $q->where('cabang_id', $request->cabang_id);
            });
        }

        if ($request->filled('status')) {
            $base->where('status', $request->string('status'));
        }
        if ($request->filled('date_from')) {
            $base->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $base->whereDate('created_at', '<=', $request->date_to);
        }
        if ($kw = $request->get('q')) {
            $base->where(function ($x) use ($kw) {
                $x->where('kode', 'like', "%{$kw}%")
                  ->orWhereHas('pelanggan', function ($q) use ($kw) {
                      $q->where('nama_pelanggan', 'like', "%{$kw}%")
                        ->orWhere('telepon', 'like', "%{$kw}%");
                  });
            });
        }

        // --- 2) Query untuk LIST: duplikasi base lalu terapkan filter 'paid' ---
        $listQuery = clone $base;
        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $listQuery->where('is_paid', (int) $paid);
        }

        // Ambil list dengan paginate
        $orders = $listQuery
            ->latest() // Shortcut untuk orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // --- 3) Statistik: dihitung dari BASE (tanpa 'paid') agar angka total benar ---
        $stats = [
            'total'       => (clone $base)->count(),
            'belum_lunas' => (clone $base)->where('is_paid', 0)->count(),
            'lunas'       => (clone $base)->where('is_paid', 1)->count(),
        ];

        return view('role.pemilik.history', compact('orders', 'cabangs', 'stats', 'kasirs'));
    }

    /**
     * Detail untuk modal (JSON).
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['pelanggan', 'creator.cabang'])->findOrFail($id);

        // Ambil log (Safety check jika tabel belum ada)
        $logs = class_exists(PesananLog::class)
            ? PesananLog::with('user:id,name')
                ->where('pesanan_id', $pesanan->id)
                ->latest()
                ->get()
            : collect();

        // Nama Cabang diambil dari Kasir yang membuat
        $namaCabang = optional($pesanan->creator->cabang)->nama_cabang ?? 'Pusat/Unknown';

        return response()->json([
            'order' => [
                'id'         => $pesanan->id,
                'kode'       => $pesanan->kode,
                'customer'   => optional($pesanan->pelanggan)->nama_pelanggan ?? 'Umum',
                'telepon'    => optional($pesanan->pelanggan)->telepon ?? '-',
                'email'      => optional($pesanan->pelanggan)->email ?? '-',
                'cabang'     => $namaCabang, // <--- Data Cabang
                'layanan'    => $pesanan->layanan,
                'berat_kg'   => (float) $pesanan->berat_kg,
                'total'      => (int) $pesanan->total,
                'status'     => $pesanan->status,
                'is_paid'    => (bool) $pesanan->is_paid,
                'created_at' => optional($pesanan->created_at)->format('d M Y H:i'),
                'kasir'      => optional($pesanan->creator)->name ?? 'Sistem',
            ],
            'logs' => $logs->map(fn ($l) => [
                'by'   => $l->user->name ?? 'System',
                'from' => $l->from_status,
                'to'   => $l->to_status,
                'note' => $l->note,
                'at'   => optional($l->created_at)->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * Export CSV.
     */
    public function exportCSV(Request $request)
    {
        // Query sama persis dengan Index
        $base = Pesanan::query()->with(['creator.cabang', 'pelanggan']);

        if ($request->filled('cabang_id')) {
            $base->whereHas('creator', function($q) use ($request) {
                $q->where('cabang_id', $request->cabang_id);
            });
        }
        
        if ($request->filled('status'))      $base->where('status', $request->string('status'));
        if ($request->filled('date_from'))   $base->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))     $base->whereDate('created_at', '<=', $request->date_to);
        
        if ($kw = $request->get('q')) {
            $base->where(function ($x) use ($kw) {
                $x->where('kode', 'like', "%{$kw}%")
                  ->orWhereHas('pelanggan', function ($q) use ($kw) {
                      $q->where('nama_pelanggan', 'like', "%{$kw}%")
                        ->orWhere('telepon', 'like', "%{$kw}%");
                  });
            });
        }

        $paid = $request->input('paid', '');
        if ($paid !== '' && in_array($paid, ['0','1'], true)) {
            $base->where('is_paid', (int) $paid);
        }

        $rows = $base->latest()->get();

        $filename = 'laundry_history_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            
            fputcsv($out, [
                'Kode','Customer','Telepon','Cabang','Layanan',
                'Berat(kg)','Total','Status','Lunas','Kasir','Waktu'
            ]);
            
            foreach ($rows as $r) {
                // Ambil cabang dari Creator
                $cabang = $r->creator->cabang->nama_cabang ?? '-';

                fputcsv($out, [
                    $r->kode,
                    optional($r->pelanggan)->nama_pelanggan ?? 'Umum',
                    optional($r->pelanggan)->telepon ?? '-',
                    $cabang, // <--- Masukkan Cabang
                    $r->layanan,
                    $r->berat_kg,
                    $r->total,
                    $r->status,
                    $r->is_paid ? 'Lunas' : 'Belum',
                    optional($r->creator)->name,
                    $r->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Ubah nama method agar sesuai route yang kita buat sebelumnya
    public function printStruk(Pesanan $id) // Pakai $id atau Model Binding
    {
        $id->loadMissing(['pelanggan', 'creator.cabang']);
        return view('role.pemilik.print.struk', ['pesanan' => $id]);
    }

    public function printTag(Pesanan $id)
    {
        $id->loadMissing(['pelanggan', 'creator.cabang']);
        return view('role.pemilik.print.tag', ['pesanan' => $id]);
    }
}