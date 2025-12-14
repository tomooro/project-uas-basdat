@extends('layouts.app')

@section('title', 'Pesanan Aktif - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold">Pesanan Aktif</h2>
        <p class="text-muted mb-4">Daftar pesanan yang sedang dikerjakan</p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Search Bar --}}
        <form method="GET" action="{{ request()->url() }}" class="mb-3">
            <div class="input-group">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    class="form-control form-control-lg"
                    placeholder="Cari kode / nama pelanggan..."
                    aria-label="Cari kode / nama pelanggan">
                <button class="btn btn-success px-4 fw-semibold" type="submit">CARI</button>
            </div>
        </form>

        <div class="card shadow-sm border-0 rounded-3 p-3">
            @forelse($orders as $order)
                @php
                    // 1. Mapping Warna Status Pesanan
                    $statusMap = [
                        'Baru'          => 'bg-primary',      // Biru
                        'Dalam Proses'  => 'bg-warning text-dark', // Kuning
                        'Siap Ambil'    => 'bg-warning text-dark', // Kuning
                        'Selesai'       => 'bg-success',      // Hijau
                        'Dibatalkan'    => 'bg-danger',       // Merah
                    ];

                    // 2. Cek Status Pembayaran
                    $isPaid = isset($order->status_pembayaran)
                                ? $order->status_pembayaran === 'lunas'
                                : (bool) ($order->is_paid ?? false);
                    
                    // 3. Warna Badge Pembayaran: Lunas (Hijau), Belum (Merah)
                    $paymentBadgeClass = $isPaid ? 'bg-success' : 'bg-danger'; 
                @endphp

                <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                    {{-- INFORMASI KIRI --}}
                    <div>
                        <h6 class="fw-bold mb-1">
                          {{ $order->kode }} - {{ optional($order->pelanggan)->nama_pelanggan ?? $order->customer_name }}
                        </h6>
                        <small class="text-muted d-block mb-1">
                            {{ $order->first_layanan_name }} • {{ number_format($order->total_berat, 2, ',', '.') }} kg
                        </small>
                        <small class="text-muted d-block mb-1">
                            Kasir: {{ $order->creator->name ?? '-' }}
                        </small>
                        <div class="mb-1">
                            {{-- Badge Status Pesanan --}}
                            <span class="badge {{ $statusMap[$order->status] ?? 'bg-secondary' }}">
                                {{ $order->status }}
                            </span>

                            {{-- Badge Status Pembayaran --}}
                            <span class="badge {{ $paymentBadgeClass }}">
                                {{ $isPaid ? 'Lunas' : 'Belum Lunas' }}
                            </span>
                        </div>
                        <p class="fw-bold mb-0">Rp{{ number_format($order->total ?? 0, 0, ',', '.') }}</p>
                    </div>

                    {{-- TOMBOL AKSI KANAN --}}
                    <div class="text-end">
                        @if($order->status !== 'Selesai' && $order->status !== 'Dibatalkan')

                            {{-- 1. TOMBOL SIAP AMBIL --}}
                            {{-- Muncul jika status belum Siap Ambil (misal: Baru). Bebas klik. --}}
                            @if($order->status !== 'Siap Ambil')
                                <form method="POST" 
                                      action="{{ route('kasir.pesanan.updateStatus', $order->id) }}" 
                                      class="d-inline"
                                      onsubmit="return confirm('Ubah status jadi Siap Ambil dan kirim WA?')">
                                    @csrf 
                                    @method('PUT') 
                                    {{-- Default controller set status ke Siap Ambil --}}
                                    <button type="submit" class="btn btn-info btn-sm text-white">
                                        Siap Ambil
                                    </button>
                                </form>
                            @endif
                            
                            {{-- 2. TOMBOL SELESAI --}}

                            @php
                                $isSiapAmbil = $order->status === 'Siap Ambil';
                            @endphp

                            {{-- KONDISI A: Status Belum Siap Ambil (Baru/Proses) --}}
                            {{-- Peringatan: Harus Siap Ambil Dulu --}}
                            @if(!$isSiapAmbil)
                                <button type="button" 
                                        class="btn btn-secondary btn-sm ms-1" 
                                        onclick="Swal.fire({
                                            icon: 'error',
                                            title: 'Aksi Ditolak!',
                                            text: 'Pesanan belum mencapai tahap Siap Ambil. Harap proses pesanan terlebih dahulu.',
                                            confirmButtonColor: '#35C8B4'
                                        })">
                                    <i class="bi bi-lock-fill"></i> Selesai
                                </button>

                            {{-- KONDISI B: Sudah Siap Ambil, tapi BELUM LUNAS --}}
                            {{-- Peringatan: Harus Lunas Dulu --}}
                            @elseif(!$isPaid)
                                <button type="button" 
                                        class="btn btn-secondary btn-sm ms-1"
                                        onclick="Swal.fire({
                                            icon: 'warning',
                                            title: 'Belum Lunas!',
                                            text: 'Pesanan sudah Siap Ambil, tetapi pembayaran masih Belum Lunas. Harap lunasi di menu Status Pembayaran sebelum menyelesaikan pesanan.',
                                            confirmButtonColor: '#35C8B4'
                                        })">
                                    <i class="bi bi-exclamation-circle-fill"></i> Selesai
                                </button>

                            {{-- KONDISI C: Sudah SIAP AMBIL dan SUDAH LUNAS --}}
                            {{-- Tombol Hijau Aktif --}}
                            @else
                                <form method="POST" 
                                        action="{{ route('kasir.pesanan.updateStatus', $order->id) }}" 
                                        class="d-inline ms-1"
                                        onsubmit="return confirm('Tandai Selesai & kirim WA?')">
                                    @csrf 
                                    @method('PUT') 
                                    <input type="hidden" name="status_akhir" value="Selesai">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        Selesai
                                    </button>
                                </form>
                            @endif        

                        @endif

                        {{-- Tombol Cetak Tag (Selalu Ada) --}}
                        <a href="{{ route('kasir.pesanan.cetakTag', $order->id) }}"
                           target="_blank"
                           class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="bi bi-printer"></i> Cetak Tag
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center mb-0">Belum ada pesanan aktif</p>
            @endforelse
            
            {{-- Pagination --}}
            <div class="mt-3">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SCRIPT LOGIC WA (TAB SAMA + ANTI BACK LOOP) --}}
@if(session('wa_url'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. PENCEGAH LOOP SAAT TOMBOL BACK DITEKAN ---
        // Kalau user balik ke sini lewat tombol Back browser, 
        // STOP script di sini. Jangan munculkan alert lagi.
        var perfEntries = performance.getEntriesByType("navigation");
        if (perfEntries.length > 0 && perfEntries[0].type === "back_forward") {
            return; 
        }

        const waUrl = "{{ session('wa_url') }}";
        const alertTitle = "{{ session('alert_title') ?? 'Berhasil' }}";

        Swal.fire({
            icon: 'success',
            title: alertTitle,
            text: 'Status diperbarui. Klik OK untuk beralih ke WhatsApp.',
            showConfirmButton: true,
            confirmButtonText: 'OK',
            confirmButtonColor: '#35C8B4',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // --- 2. BERSIHKAN JEJAK URL (History) ---
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.pathname);
                }
                
                // --- 3. PINDAH KE WA (DI TAB YANG SAMA) ---
                window.location.href = waUrl;
            }
        });
    });
</script>
@endif

{{-- Logic Alert Biasa --}}
@if(session('success') && !session('wa_url'))
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Cek Back Button juga untuk alert biasa
        var perfEntries = performance.getEntriesByType("navigation");
        if (perfEntries.length > 0 && perfEntries[0].type === "back_forward") {
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            confirmButtonColor: '#35C8B4'
        });
    });
</script>
@endif

<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent  { background-color: #35C8B4 !important; }
</style>
@endsection