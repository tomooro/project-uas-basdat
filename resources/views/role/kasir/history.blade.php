@extends('layouts.app')
@section('title','History Pesanan - LaundryKita')

@section('content')

{{-- STRUKTUR FLEXBOX (Sama persis seperti halaman Profil) --}}
<div class="d-flex">
    
    {{-- 1. SIDEBAR --}}
    @include('role.kasir.partials.sidebar')

    {{-- 2. KONTEN UTAMA --}}
    <div class="flex-grow-1 p-4" style="background:#f1f5f4ff;">
        
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">History Pesanan</h2>
                <p class="text-muted mb-0">Semua pesanan (read-only)</p>
            </div>
        </div>

        {{-- STATISTIK --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-primary"><i class="bi bi-receipt fs-4"></i></div>
                        <div>
                            <div class="text-muted small">Total Pesanan</div>
                            <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-success"><i class="bi bi-check-circle fs-4"></i></div>
                        <div>
                            <div class="text-muted small">Lunas</div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($stats['lunas']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-danger"><i class="bi bi-exclamation-circle fs-4"></i></div>
                        <div>
                            <div class="text-muted small">Belum Lunas</div>
                            <div class="fs-4 fw-bold text-danger">{{ number_format($stats['belum_lunas']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER FORM --}}
        <form class="card shadow-sm border-0 p-3 mb-4" method="get" action="{{ route('kasir.history') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach(['Baru','Siap Ambil','Selesai'] as $st)
                            <option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small fw-bold">Pembayaran</label>
                    <select name="paid" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="1" {{ request('paid')==='1'?'selected':'' }}>Lunas</option>
                        <option value="0" {{ request('paid')==='0'?'selected':'' }}>Belum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small fw-bold">Dari</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1 small fw-bold">Sampai</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small fw-bold">Cari</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Cari...">
                        <button class="btn btn-accent btn-sm text-white">Filter</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- LIST PESANAN --}}
        @forelse($orders as $order)
            @php
                $warnaStatus = match($order->status) {
                    'Baru' => 'bg-primary',
                    'Siap Ambil' => 'bg-warning text-dark',
                    'Selesai' => 'bg-success',
                    default => 'bg-secondary'
                };
                $warnaPaid = $order->is_paid ? 'bg-success' : 'bg-danger';
            @endphp

            <div class="card shadow-sm border-0 mb-2">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        {{-- Baris 1: Kode & Nama Pelanggan --}}
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0 text-dark">
                                {{ $order->kode }} - {{ optional($order->pelanggan)->nama_pelanggan ?? 'Umum' }}
                            </h6>
                        </div>

                        {{-- Baris 2: Info Layanan, Berat, Waktu --}}
                        <div class="text-muted small mb-2">
                            {{ $order->layanan }} • {{ number_format((float)$order->berat_kg, 2, ',', '.') }} kg • 
                            {{ optional($order->created_at)->format('d M H:i') }}
                        </div>

                        {{-- Baris 3: Status & Kasir (Sejajar) --}}
                        <div class="d-flex align-items-center gap-2">
                            {{-- Badge Status Pesanan --}}
                            <span class="badge {{ $warnaStatus }} rounded-pill" style="font-size: 0.75em;">
                                {{ $order->status }}
                            </span>
                            
                            {{-- Badge Status Pembayaran --}}
                            <span class="badge {{ $warnaPaid }} rounded-pill" style="font-size: 0.75em;">
                                {{ $order->is_paid ? 'Lunas' : 'Belum Lunas' }}
                            </span>

                            {{-- Info Kasir (Baru Ditambahkan) --}}
                            <span class="badge bg-light text-secondary border rounded-pill" style="font-size: 0.75em;">
                                <i class="bi bi-person-circle me-1"></i> {{ $order->creator->name ?? 'System' }}
                            </span>
                        </div>
                    </div>

                    {{-- Bagian Kanan: Total & Tombol Detail --}}
                    <div class="text-end">
                        <div class="fw-bold text-dark mb-1">Rp{{ number_format((int)$order->total, 0, ',', '.') }}</div>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-3"
                                style="font-size: 0.85rem;"
                                data-id="{{ $order->id }}"
                                onclick="openDetail(this)">
                            Detail
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-center py-4">Belum ada data pesanan.</div>
        @endforelse

        <div class="mt-3">
            {{ $orders->links() }}
        </div>

    </div> {{-- End Flex Grow --}}
</div> {{-- End D-Flex --}}

{{-- MODAL DETAIL --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
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

{{-- STYLE & SCRIPT TAMBAHAN --}}
@push('styles')
<style>
    .text-accent { color:#35C8B4!important; }
    .bg-accent   { background-color:#35C8B4!important; }
    .btn-accent  { background:#35C8B4;color:#fff;border-radius:25px;padding:5px 15px;font-weight:500;border:none; }
    .btn-accent:hover { background:#2ba497;color:#fff; }
</style>
@endpush

@push('scripts')
<script>
async function openDetail(btn){
  const id = btn.getAttribute('data-id');
  try {
    const res = await fetch(`{{ url('/kasir/history') }}/${id}`, {
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    });
    if (!res.ok) throw new Error('Gagal memuat data');
    const data = await res.json();
    const order = data.order;
    const logs  = data.logs || [];

    let getBadgeClass = (status) => {
        if (status === 'Baru') return 'bg-primary';
        if (status === 'Siap Ambil') return 'bg-warning text-dark';
        if (status === 'Selesai') return 'bg-success';
        return 'bg-secondary';
    };

    const htmlLogs = logs.length > 0
      ? `<ul class="list-group list-group-flush">
          ${logs.map(l => `
            <li class="list-group-item py-2">
              <div class="d-flex justify-content-between">
                  <span><strong>${l.by}</strong>: <span class="badge ${getBadgeClass(l.from)}">${l.from}</span> → <span class="badge ${getBadgeClass(l.to)}">${l.to}</span></span>
                  <small class="text-muted">${l.at}</small>
              </div>
              ${l.note ? `<div class="small fst-italic text-muted mt-1">Catatan: ${l.note}</div>` : ''}
            </li>
          `).join('')}
         </ul>`
      : '<div class="text-muted">Belum ada log perubahan.</div>';

    let statusBadgeModal = `<span class="badge ${getBadgeClass(order.status)}">${order.status}</span>`;
    let paidBadgeModal = `<span class="badge ${order.is_paid ? 'bg-success' : 'bg-danger'}">${order.is_paid ? 'Lunas' : 'Belum Lunas'}</span>`;

    document.getElementById('detailBody').innerHTML = `
      <div class="card border-0 shadow-sm mb-3 bg-white">
        <div class="card-body p-3">
          <div class="row g-2">
            <div class="col-md-6">
              <p class="mb-1"><strong>Kode:</strong> ${order.kode}</p>
              <p class="mb-1"><strong>Pelanggan:</strong> ${order.customer}</p>
              <p class="mb-1"><strong>Telepon:</strong> ${order.telepon || '-'}</p>
              <p class="mb-1"><strong>Kasir:</strong> ${order.kasir || '-'}</p> </div>
            <div class="col-md-6">
              <p class="mb-1"><strong>Layanan:</strong> ${order.layanan}</p>
              <p class="mb-1"><strong>Total:</strong> Rp${Number(order.total || 0).toLocaleString('id-ID')}</p>
              <p class="mb-1"><strong>Status:</strong> ${statusBadgeModal} ${paidBadgeModal}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="card border-0 shadow-sm bg-white">
        <div class="card-body p-3">
          <h6 class="fw-bold mb-2">Log Perubahan</h6>
          ${htmlLogs}
        </div>
      </div>
    `;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
  } catch (err) {
    console.error(err);
    alert('Terjadi kesalahan saat memuat detail.');
  }
}
</script>
@endpush

@endsection