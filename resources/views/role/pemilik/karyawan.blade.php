@extends('layouts.app')

@section('title', 'Kelola Karyawan - LaundryKita')

@section('content')
<div class="d-flex">

    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-3">Manajemen Karyawan</h2>
                <p class="text-muted mb-0">Atur dan kelola karyawan di LaundryKita</p>
            </div>
            <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#tambahKaryawanModal">
                + Tambah Karyawan
            </button>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- === Credensial kasir baru / reset (tampil sekali) === --}}
        @if(session('temp_pass') && session('temp_email'))
            <div class="alert alert-warning d-flex align-items-center shadow-sm border-0" role="alert">
                <div class="w-100">
                    <h5 class="alert-heading fw-bold"><i class="bi bi-key"></i> Informasi Login Karyawan</h5>
                    <hr>
                    <p class="mb-1">Email: <code class="fs-5 text-dark">{{ session('temp_email') }}</code></p>
                    <p class="mb-2">Password Sementara: <code class="fs-5 text-dark">{{ session('temp_pass') }}</code></p>
                    <div class="small text-muted fst-italic">
                        *Mohon catat atau berikan informasi ini kepada karyawan. Password wajib diganti setelah login pertama.
                    </div>
                </div>
            </div>
        @endif

        <h5 class="fw-bold mb-3">Daftar Karyawan ({{ $karyawans->count() }})</h5>

        {{-- LOOP DINAMIS KASIR --}}
        @forelse($karyawans as $k)
            <div class="card shadow-sm border-0 rounded-3 p-3 mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1 fs-5">{{ $k->name }}</h6>
                        <div class="text-muted mb-2">
                            <span class="small"><i class="bi bi-envelope"></i> {{ $k->email }}</span>
                            <span class="mx-1">•</span>
                            <span class="small"><i class="bi bi-telephone"></i> {{ $k->phone ?? '-' }}</span>
                        </div>
                        
                        {{-- Badge Nama Cabang --}}
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-shop-window"></i> {{ $k->cabang->nama_cabang ?? 'Belum ada cabang' }}
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        {{-- Edit --}}
                        <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#editKaryawanModal-{{ $k->id }}">
                            Edit
                        </button>

                        {{-- Reset Password --}}
                        <form action="{{ route('pemilik.karyawan.reset', $k->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-secondary"
                                onclick="return confirm('Reset password akan membuat password acak baru. Lanjutkan?')">
                                Reset Pass
                            </button>
                        </form>

                        {{-- Hapus --}}
                        <form method="POST" action="{{ route('pemilik.karyawan.destroy', $k->id) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Yakin ingin menghapus karyawan ini?')">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal Edit (Per User) --}}
            <div class="modal fade" id="editKaryawanModal-{{ $k->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content rounded-3 border-0 shadow">
                  <div class="modal-header bg-accent text-white">
                    <h5 class="modal-title">Edit Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <form method="POST" action="{{ route('pemilik.karyawan.update', $k->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        {{-- Nama --}}
                        <div class="mb-3">
                          <label class="form-label fw-bold">Nama Lengkap</label>
                          <input type="text" name="name" class="form-control" value="{{ $k->name }}" required>
                        </div>
                        {{-- Email --}}
                        <div class="mb-3">
                          <label class="form-label fw-bold">Email</label>
                          <input type="email" name="email" class="form-control" value="{{ $k->email }}" required>
                        </div>
                        {{-- Telepon --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. Telepon</label>
                            <input type="text" name="phone" class="form-control" value="{{ $k->phone }}">
                        </div>
                        {{-- Dropdown Cabang (Edit) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Penempatan Cabang</label>
                            <select name="cabang_id" class="form-select" required>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($cabangs as $c)
                                    <option value="{{ $c->id }}" {{ $k->cabang_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                      <button class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal" type="button">Batalkan</button>
                      <button class="btn btn-accent" type="submit">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
        @empty
            <div class="text-center p-5 text-muted border rounded bg-white">
                <i class="bi bi-people fs-1 d-block mb-3"></i>
                <p>Belum ada data karyawan. Silakan tambah baru.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="tambahKaryawanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-3 border-0 shadow">
      <div class="modal-header bg-accent text-white">
        <h5 class="modal-title">Tambah Karyawan Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formTambahKaryawan" method="POST" action="{{ route('pemilik.karyawan.store') }}">
        @csrf
        <div class="modal-body">
          {{-- Nama --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
          </div>
          {{-- Email --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Email Login <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="Contoh: budi@laundrykita.com" required>
          </div>
          {{-- Telepon --}}
          <div class="mb-3">
            <label class="form-label fw-bold">No. Telepon</label>
            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxx">
          </div>
          {{-- Dropdown Cabang (Tambah) --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Penempatan Cabang <span class="text-danger">*</span></label>
            <select name="cabang_id" class="form-select" required>
                <option value="">-- Pilih Cabang --</option>
                @foreach($cabangs as $c)
                    <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                @endforeach
            </select>
            <div class="form-text">Karyawan hanya bisa mengakses data cabang ini.</div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal" type="button">Batalkan</button>
          <button class="btn btn-accent" type="submit">Tambahkan Karyawan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Alert Sukses / Gagal (Menggunakan SweetAlert)
    @if(session('success'))
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: "{{ session('success') }}", 
            confirmButtonColor: '#35C8B4' 
        });
    @endif
    
    @if(session('error'))
        Swal.fire({ 
            icon: 'error', 
            title: 'Gagal!', 
            text: "{{ session('error') }}", 
            confirmButtonColor: '#d33' 
        });
    @endif
</script>
@endpush

<style>
    /* Style sesuai request (Hijau Tosca Accent) */
    .text-accent { color: #35C8B4 !important; }
    .bg-accent  { background-color: #35C8B4 !important; }
    
    .btn-accent {
        background: #35C8B4; 
        color: #fff; 
        border-radius: 25px; /* Tombol bulat manis */
        padding: 8px 20px; 
        font-weight: 600; 
        border: none;
        transition: 0.3s;
    }
    .btn-accent:hover { 
        background: #2ba497; 
        color: #fff; 
        transform: translateY(-2px); /* Efek naik dikit pas hover */
    }

    .form-label {
        font-size: 0.9rem;
        color: #555;
    }
</style>
@endsection