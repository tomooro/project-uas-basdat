@extends('layouts.app')

@section('title', 'Status Pembayaran - LaundryKita')

@section('content')

{{-- Struktur Flexbox (Manual Sidebar) --}}
<div class="d-flex">
    
    {{-- 1. SIDEBAR --}}
    @include('role.kasir.partials.sidebar')

    {{-- 2. KONTEN UTAMA --}}
    <div class="flex-grow-1 p-4" style="background:#f1f5f4ff;">
        
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Status Pembayaran</h2>
                <p class="text-muted mb-0">Pilih pesanan untuk mengubah status pembayarannya</p>
            </div>
        </div>

        {{-- Flash message --}}
        @if(session('ok'))      <div class="alert alert-success border-0 shadow-sm">{{ session('ok') }}</div>@endif
        @if(session('success')) <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>@endif
        @if(session('error'))   <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>@endif

        <div class="row">
            {{-- KOLOM KIRI: Daftar Pesanan --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3 p-4 h-100">
                    <h5 class="fw-bold mb-3">Daftar Pesanan</h5>

                    {{-- FILTER & SEARCH --}}
                    <form class="mb-3" method="GET" action="{{ route('kasir.pembayaran.index') }}">
                        <div class="row g-2">
                            {{-- Dropdown Filter Lunas/Belum (Permintaan Kamu) --}}
                            <div class="col-md-4">
                                <select name="status_bayar" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Semua</option>
                                    <option value="belum_lunas" {{ request('status_bayar') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                    <option value="lunas" {{ request('status_bayar') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </div>

                            {{-- Search Box --}}
                            <div class="col-md-8">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama/kode...">
                                    <button class="btn btn-accent text-white" type="submit">Cari</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- LIST PESANAN --}}
                    @forelse($orders as $p)
                        @php
                            $isLunas    = $p->is_paid;
                            $badgeClass = $isLunas ? 'bg-success' : 'bg-danger';
                            $badgeText  = $isLunas ? 'Lunas' : 'Belum Lunas';
                            $namaCust   = $p->pelanggan->nama_pelanggan ?? '-';
                            $namaLayanan= $p->details->first()->layanan->nama ?? '-';
                        @endphp

                        <div class="card border-0 shadow-sm p-3 mb-2 bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold text-dark">{{ $p->kode }} - {{ $namaCust }}</div>
                                    <div class="text-muted small">
                                        {{ $namaLayanan }}
                                        @if(!is_null($p->details->first()->berat_kg))
                                            • {{ number_format((float)$p->details->first()->berat_kg, 2) }}kg
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge {{ $badgeClass }}" style="font-size: 0.7em;">{{ $badgeText }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-6">Rp{{ number_format($p->total, 0, ',', '.') }}</div>
                                    <div class="mt-2">
                                        {{-- Tombol Pilih --}}
                                        <a href="{{ route('kasir.pembayaran.index', array_merge(request()->query(), ['id' => $p->id])) }}"
                                           class="btn btn-sm btn-outline-primary px-3 py-0" 
                                           style="font-size: 0.85rem;">
                                            Pilih
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted border rounded bg-light">
                            <small>Tidak ada data pesanan.</small>
                        </div>
                    @endforelse

                    <div class="mt-3">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Detail & Aksi (Struk) --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 rounded-3 p-4 h-100">
                    <h5 class="fw-bold mb-3">Rincian Pembayaran</h5>

                    @if(isset($selected) && !empty($selected))
                        @php
                            $isLunasSel    = $selected->is_paid;
                            $badgeClassSel = $isLunasSel ? 'bg-success' : 'bg-danger';
                            $badgeTextSel  = $isLunasSel ? 'Lunas' : 'Belum Lunas';
                            $selCust       = $selected->pelanggan->nama_pelanggan ?? '-';
                            $selLayanan    = $selected->details->first()->layanan->nama ?? '-';
                            $selBerat      = $selected->details->first()->berat_kg ?? null;
                        @endphp

                        {{-- AREA STRUK (ID ini dipakai JS untuk print) --}}
                        <div id="receipt-content" class="border p-3 rounded mb-3 bg-white">
                            <h6 class="fw-bold mb-1 text-accent text-center">LaundryKita</h6>
                            <div class="small text-muted text-center mb-2">Dicetak: {{ now()->format('d M Y, H:i') }}</div>
                            <hr class="my-2 border-secondary border-opacity-25">
                            
                            <div class="small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Order ID:</span> <span class="fw-bold">{{ $selected->kode }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Customer:</span> <span>{{ $selCust }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Kasir:</span> <span>{{ $selected->creator->name ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Layanan:</span> 
                                    <span>{{ $selLayanan }} @if($selBerat) ({{ $selBerat }}kg) @endif</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 mt-2">
                                    <span class="fw-bold">Total Tagihan:</span> 
                                    <span class="fw-bold text-dark">Rp{{ number_format($selected->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span>Status:</span>
                                    <span class="badge {{ $badgeClassSel }}">{{ $badgeTextSel }}</span>
                                </div>
                                @if(!empty($selected->paid_at))
                                    <div class="d-flex justify-content-between mt-1 text-success">
                                        <span>Lunas Tgl:</span> 
                                        <span>{{ \Carbon\Carbon::parse($selected->paid_at)->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <hr class="my-2 border-secondary border-opacity-25">
                            <p class="text-center text-muted mb-0" style="font-size: 0.75rem;">Terima kasih!</p>
                        </div>

                        {{-- TOMBOL AKSI --}}
                        <div class="d-flex flex-column gap-2">
                            {{-- Jika Belum Lunas: Tampilkan Form Bayar --}}
                            @unless($isLunasSel)
                                <form id="formTandaiLunas" method="POST" action="{{ route('kasir.pembayaran.markPaid', $selected->id) }}">
                                    @csrf @method('PATCH')
                                    <div class="mb-2">
                                        <label class="form-label fw-bold small">Metode Pembayaran</label>
                                        <select class="form-select form-select-sm" id="metode_pembayaran" name="metode_pembayaran">
                                            <option value="">-- Pilih Metode --</option>
                                            <option value="Tunai">Tunai</option>
                                            <option value="Transfer">Transfer</option>
                                            <option value="QRIS">QRIS</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-success w-100 fw-bold">
                                        <i class="bi bi-cash-stack me-1"></i> Tandai Lunas
                                    </button>
                                </form>
                            @endunless
                            
                            {{-- Tombol Cetak Struk --}}
                            <button type="button" onclick="printReceipt()" class="btn btn-accent w-100 fw-bold text-white">
                                <i class="bi bi-printer me-1"></i> Cetak Struk
                            </button>
                        </div>

                    @else
                        {{-- Placeholder jika belum pilih --}}
                        <div class="text-center py-5 h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                            <i class="bi bi-arrow-left-circle display-1 mb-3 text-secondary opacity-25"></i>
                            <p>Silakan pilih pesanan dari daftar di sebelah kiri.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Style Tambahan --}}
<style>
    .text-accent { color:#35C8B4 !important; }
    .bg-accent { background-color:#35C8B4 !important; }
    .btn-accent {
        background:#35C8B4; color:#fff; border-radius:8px;
        padding:6px 18px; font-weight:500; border:none; transition: 0.3s;
    }
    .btn-accent:hover { background:#2ca697; color:#fff; transform: translateY(-1px); }
    .bg-danger { background-color: #dc3545 !important; color: white; }
    
    /* Perbaiki tampilan card agar rapi */
    .card { border-radius: 12px !important; }
</style>

{{-- Script SweetAlert & Print --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Logic Form Tandai Lunas
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formTandaiLunas');
        if (form) {
            form.addEventListener('submit', function (e) {
                const metode = document.getElementById('metode_pembayaran').value;
                if (metode === '') {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Metode Kosong',
                        text: 'Silakan pilih metode pembayaran terlebih dahulu!',
                        confirmButtonColor: '#35C8B4'
                    });
                }
            });
        }
    });

    // Logic Cetak Struk
    function printReceipt() {
        // Cek status lunas dari badge yang ada di layar
        // Kita cari badge di dalam #receipt-content
        const statusBadge = document.querySelector('#receipt-content .badge');
        
        // Validasi: Harus ada badge dan teksnya harus 'Lunas'
        if (!statusBadge || statusBadge.textContent.trim().toLowerCase() !== 'lunas') {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Lunas!',
                text: 'Harap tandai lunas terlebih dahulu sebelum mencetak struk.',
                confirmButtonColor: '#35C8B4'
            });
            return;
        }

        const receiptContent = document.getElementById('receipt-content').innerHTML;
        const win = window.open('', '', 'width=400,height=600');
        win.document.write(`
            <html>
            <head>
                <title>Struk Pembayaran</title>
                <style>
                    body { font-family: 'Courier New', Courier, monospace; padding: 20px; font-size: 14px; }
                    h6 { font-size: 18px; margin: 0 0 5px; text-align: center; }
                    .text-center { text-align: center; }
                    hr { border-top: 1px dashed #000; margin: 10px 0; }
                    .d-flex { display: flex; justify-content: space-between; margin-bottom: 5px; }
                    .fw-bold { font-weight: bold; }
                    .badge { border: 1px solid #000; padding: 2px 5px; border-radius: 4px; font-size: 12px; }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
            </html>
        `);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); }, 500); // Delay dikit biar loading style selesai
    }
</script>
@endsection