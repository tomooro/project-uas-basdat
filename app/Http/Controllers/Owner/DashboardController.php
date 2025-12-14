<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Waktu real-time
        $now        = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart  = $now->copy()->startOfWeek();   // Senin
        $monthStart = $now->copy()->startOfMonth();  // Tanggal 1

        /*
        |--------------------------------------------------------------------------
        | PENDAPATAN (SUM DARI TABEL PEMBAYARANS)
        |--------------------------------------------------------------------------
        | Ini WAJIB supaya data seeder kebaca dan konsisten dengan History
        */
        $pendapatanHariIni = (int) Pembayaran::whereBetween(
            'created_at',
            [$todayStart, $now]
        )->sum('jumlah');

        /*
        |--------------------------------------------------------------------------
        | RINGKASAN PENDAPATAN
        |--------------------------------------------------------------------------
        */
        $ringkasan = [
            'hari'   => (int) Pembayaran::whereBetween('created_at', [$todayStart, $now])->sum('jumlah'),
            'minggu' => (int) Pembayaran::whereBetween('created_at', [$weekStart, $now])->sum('jumlah'),
            'bulan'  => (int) Pembayaran::whereBetween('created_at', [$monthStart, $now])->sum('jumlah'),
        ];

        /*
        |--------------------------------------------------------------------------
        | PESANAN
        |--------------------------------------------------------------------------
        */
        // Semua pesanan yang dibuat hari ini (tanpa filter status)
        $pesananHariIni = (int) Pesanan::whereDate(
            'created_at',
            $now->toDateString()
        )->count();

        // Pesanan aktif (belum selesai)
        $pesananAktif = (int) Pesanan::whereIn('status', [
            'Baru',
            'Dalam Proses',
            'Siap Ambil'
        ])->count();

        /*
        |--------------------------------------------------------------------------
        | KARYAWAN
        |--------------------------------------------------------------------------
        */
        $karyawanAktif = (int) User::where('role', 'kasir')->count();

        /*
        |--------------------------------------------------------------------------
        | LAYANAN TERPOPULER (30 HARI TERAKHIR)
        |--------------------------------------------------------------------------
        */
        $layananTerpopuler = DB::table('pesanan_details')
            ->join('layanans', 'pesanan_details.layanan_id', '=', 'layanans.id')
            ->select(
                'layanans.nama',
                DB::raw('COUNT(*) as pesanan')
            )
            ->where(
                'pesanan_details.created_at',
                '>=',
                $now->copy()->subDays(30)
            )
            ->groupBy('layanans.nama')
            ->orderByDesc('pesanan')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'nama'    => $row->nama,
                    'pesanan' => (int) $row->pesanan,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */
        return view('role.pemilik.pemilik', [
            'pendapatanHariIni' => $pendapatanHariIni,
            'pesananHariIni'    => $pesananHariIni,
            'pesananAktif'      => $pesananAktif,
            'karyawanAktif'     => $karyawanAktif,
            'ringkasan'         => $ringkasan,
            'layananTop'        => $layananTerpopuler,
            'growthPendapatan'  => null,
            'growthPesanan'     => null,
        ]);
    }
}
