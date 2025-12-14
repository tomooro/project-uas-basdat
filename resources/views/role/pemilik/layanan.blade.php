@extends('layouts.app')

@section('title', 'Kelola Layanan - LaundryKita')

@section('content')
<div class="d-flex">

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-3">Manajemen Layanan</h2>
                <p class="text-muted mb-0">Kelola layanan laundry, harga, dan waktu pengerjaan</p>
            </div>
            <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#tambahLayananModal">
                + Tambah Layanan
            </button>
        </div>

        {{-- Flash & error dari server --}}
        @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <!-- Statistik (dinamis) -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <h6 class="text-muted">Total Layanan</h6>
                    <h3 class="fw-bold text-primary">{{ $total }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <h6 class="text-muted">Harga Rata-rata</h6>
                    <h3 class="fw-bold text-primary">
                        Rp {{ number_format($avgPrice,0,',','.') }}/kg
                    </h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 p-3">
                    <h6 class="text-muted">Rata-rata Waktu Pengerjaan</h6>
                    <h3 class="fw-bold text-primary">{{ $avgDurasi }} jam</h3>
                </div>
            </div>
        </div>

        <!-- Daftar Layanan (dinamis) -->
        <h5 class="fw-bold mb-3">Daftar Layanan</h5>

        @forelse($items as $svc)
            <div class="card shadow-sm border-0 rounded-3 p-3 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $svc->nama }}</h6>
                        <small class="text-muted">
                            ID: {{ $svc->kode }} • Rp {{ number_format($svc->harga,0,',','.') }}/kg • {{ $svc->durasi_jam }} jam
                            @if(!$svc->is_active) • <span class="text-danger">Nonaktif</span>@endif
                        </small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editLayananModal-{{ $svc->id }}">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('pemilik.layanan.destroy', $svc->id) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus layanan ini?')">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Edit -->
            <div class="modal fade" id="editLayananModal-{{ $svc->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content rounded-3 border-0 shadow">
                  <div class="modal-header bg-accent text-white">
                    <h5 class="modal-title">Edit Layanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <form method="POST" action="{{ route('pemilik.layanan.update', $svc->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" name="nama" value="{{ $svc->nama }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Harga per Kg</label>
                        <input type="number" class="form-control" name="harga" value="{{ $svc->harga }}" min="0" step="1000" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Waktu Pengerjaan (jam)</label>
                        <input type="number" class="form-control" name="durasi_jam" value="{{ $svc->durasi_jam }}" min="1" required>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="act{{ $svc->id }}" name="is_active" {{ $svc->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="act{{ $svc->id }}">Aktif</label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batalkan</button>
                      <button class="btn btn-accent" type="submit">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        @empty
            <div class="card shadow-sm border-0 rounded-3 p-3 mb-2">
                <small class="text-muted">Belum ada layanan.</small>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahLayananModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 border-0 shadow">
      <div class="modal-header bg-accent text-white">
        <h5 class="modal-title">Tambah Layanan Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('pemilik.layanan.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Layanan</label>
            <input type="text" class="form-control" name="nama" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Harga per Kg</label>
            <input type="number" class="form-control" name="harga" min="0" step="1000" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Waktu Pengerjaan (jam)</label>
            <input type="number" class="form-control" name="durasi_jam" min="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batalkan</button>
          <button class="btn btn-accent" type="submit">Tambahkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
    .text-accent { color: #35C8B4 !important; }
    .bg-accent { background-color: #35C8B4 !important; }
    .btn-accent {
        background: #35C8B4;
        color: #fff;
        border-radius: 25px;
        padding: 8px 20px;
        font-weight: 500;
        border: none;
    }
    .btn-accent:hover {
        background: #2ba497;
        color: #fff;
    }
</style>

<script>
document.addEventListener('keydown', function(e) {
  const input = e.target;
  if (!input.matches('input[name="harga"]')) return;

  const cur = parseInt(input.value || 0, 10);

  if (e.key === 'ArrowUp') {
    e.preventDefault();
    input.value = cur + 1000;
  } else if (e.key === 'ArrowDown') {
    e.preventDefault();
    input.value = Math.max(0, cur - 1000);
  }
});
</script>
@endsection
