@extends('layouts.app')
@section('title', 'Kelola Cabang - LaundryKita')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Data Cabang</h2>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addCabangModal">
        + Tambah Cabang
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                    <tr>
                        <th class="px-4 py-3">Nama Cabang</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3">Alamat</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cabangs as $cabang)
                        <tr>
                            <td class="px-4 fw-bold text-dark">{{ $cabang->nama_cabang }}</td>
                            <td class="px-4">{{ $cabang->telepon ?? '-' }}</td>
                            <td class="px-4 text-muted small" style="max-width: 250px;">
                                {{ Str::limit($cabang->alamat, 40) }}
                            </td>
                            <td class="px-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    {{-- Edit --}}
                                    <button class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCabangModal{{ $cabang->id }}">
                                        Edit
                                    </button>

                                    {{-- Hapus --}}
                                    <form action="{{ route('pemilik.cabang.destroy', $cabang->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus cabang ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL EDIT --}}
                        <div class="modal fade" id="editCabangModal{{ $cabang->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('pemilik.cabang.update', $cabang->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content rounded-3 border-0 shadow">
                                        <div class="modal-header bg-accent text-white">
                                            <h5 class="modal-title">Edit Cabang</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama Cabang</label>
                                                <input type="text" name="nama_cabang" class="form-control" value="{{ $cabang->nama_cabang }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">No. Telepon</label>
                                                <input type="text" name="telepon" class="form-control" value="{{ $cabang->telepon }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Alamat</label>
                                                <textarea name="alamat" class="form-control" rows="3">{{ $cabang->alamat }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                                            <button type="submit" class="btn btn-accent">Simpan Perubahan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                Belum ada data cabang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="addCabangModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('pemilik.cabang.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header bg-accent text-white">
                    <h5 class="modal-title">Tambah Cabang Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Cabang</label>
                        <input type="text" name="nama_cabang" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">No. Telepon</label>
                        <input type="text" name="telepon" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                    <button type="submit" class="btn btn-accent">Simpan</button>
                </div>
            </div>
        </form>
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
@endpush
