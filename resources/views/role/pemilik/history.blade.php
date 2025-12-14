@extends('layouts.app')
@section('title','History Pesanan - LaundryKita')

@section('content')

<h2 class="fw-bold mb-3">History Pesanan</h2>
<p class="text-muted">Semua pesanan dari seluruh cabang (read-only)</p>

{{-- Statistik ringkas --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="text-muted">Total Pesanan (Terfilter)</div>
            <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="text-muted">Lunas</div>
            <div class="fs-4 fw-bold text-success">{{ number_format($stats['lunas']) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 p-3">
            <div class="text-muted">Belum Lunas</div>
            <div class="fs-4 fw-bold text-danger">{{ number_format($stats['belum_lunas']) }}</div>
        </div>
    </div>
</div>

{{-- Filter Form --}}
<form class="card shadow-sm border-0 p-3 mb-4" method="get" action="{{ route('pemilik.history') }}">
    <div class="row g-1 align-items-end">

        {{-- Cabang --}}
        <div class="col-md-2">
            <label class="form-label mb-0 fw-bold small">Cabang</label>
            <select name="cabang_id" class="form-select form-select-sm">
                <option value="">Semua Cabang</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->nama_cabang }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Kasir --}}
        <div class="col-md-2">
            <label class="form-label mb-0 fw-bold small">Kasir</label>
            <select name="kasir_id" class="form-select form-select-sm">
                <option value="">Semua Kasir</option>
                @foreach($kasirs as $k)
                    <option value="{{ $k->id }}" {{ request('kasir_id') == $k->id ? 'selected' : '' }}>
                        {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="col-md-1">
            <label class="form-label mb-0 fw-bold small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(['Baru','Siap Ambil','Selesai'] as $st)
                    <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        {{-- Pembayaran (DIPERLEBAR) --}}
        <div class="col-md-2">
            <label class="form-label mb-0 fw-bold small">Pembayaran</label>
            <select name="paid" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="1" {{ request('paid')==='1'?'selected':'' }}>Lunas</option>
                <option value="0" {{ request('paid')==='0'?'selected':'' }}>Belum</option>
            </select>
        </div>

        {{-- Dari --}}
        <div class="col-md-1">
            <label class="form-label mb-0 fw-bold small">Dari</label>
            <input type="date" name="date_from" class="form-control form-control-sm px-1" value="{{ request('date_from') }}">
        </div>

        {{-- Sampai --}}
        <div class="col-md-1">
            <label class="form-label mb-0 fw-bold small">Sampai</label>
            <input type="date" name="date_to" class="form-control form-control-sm px-1" value="{{ request('date_to') }}">
        </div>

        {{-- Cari --}}
        <div class="col-md-3">
            <label class="form-label mb-0 fw-bold small">Cari</label>
            <div class="d-flex gap-1">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Kode/Nama" value="{{ request('q') }}">
                <button class="btn btn-accent btn-sm">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Export --}}
    <div class="mt-2 text-end">
         <a class="btn btn-secondary btn-sm" href="{{ route('pemilik.history.export', request()->query()) }}">
            Export CSV
         </a>
    </div>
</form>

{{-- List pesanan --}}
@forelse($orders as $order)
    @php
        $paidClass = $order->is_paid ? 'bg-success' : 'bg-danger';
        $statusClass = $order->status == 'Baru' ? 'bg-primary' :
                       ($order->status == 'Siap Ambil' ? 'bg-warning text-dark' :
                       ($order->status == 'Selesai' ? 'bg-success' : 'bg-secondary'));

        $namaCabang = $order->creator->cabang->nama_cabang ?? 'Pusat/Unknown';
    @endphp

    <div class="card shadow-sm border-0 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="fw-bold mb-1">
                    {{ $order->kode }} - {{ optional($order->pelanggan)->nama_pelanggan ?? 'Nama Pelanggan Tidak Tersedia' }}
                </h5>

                <div class="text-muted mb-2">
                    <span class="badge bg-info text-dark mb-1">
                        <i class="bi bi-shop"></i> {{ $namaCabang }}
                    </span><br>
                    <span class="small">Kasir: {{ $order->creator->name ?? 'Dihapus' }}</span> • 
                    {{ $order->layanan }} • {{ number_format((float)$order->berat_kg, 2, ',', '.') }} kg
                    • <span class="fw-bold text-dark">{{ optional($order->created_at)->format('d M Y H:i') }}</span>
                </div>

                <div>
                    <span class="badge {{ $paidClass }}">{{ $order->is_paid ? 'Lunas' : 'Belum Lunas' }}</span>
                    <span class="badge {{ $statusClass }}">{{ $order->status }}</span>
                </div>
            </div>

            <div class="text-end">
                <div class="fw-bold fs-5">
                    Rp{{ number_format((int)$order->total, 0, ',', '.') }}
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-dark"
                            data-id="{{ $order->id }}"
                            onclick="openDetail(this)">
                        Detail
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-light border text-center py-4">
        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
        Belum ada data pesanan yang sesuai filter.
    </div>
@endforelse

<div class="mt-3">
    {{ $orders->links() }}
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header bg-accent text-white">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background:#f7fffd;">
                <div id="detailBody" class="p-2 text-center text-muted">Memuat...</div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent { background-color: #35C8B4 !important; }
    .btn-accent {
        background: #35C8B4;
        color: #fff;
        border-radius: 20px;
        padding: 4px 12px;
        font-weight: 500;
        border: none;
    }
    .btn-accent:hover {
        background: #2ba497;
        color: #fff;
    }
</style>
@endpush
