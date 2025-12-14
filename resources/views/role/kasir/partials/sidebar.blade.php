{{-- resources/views/role/kasir/partials/sidebar.blade.php --}}
<div class="sidebar bg-white border-end d-flex flex-column" style="width:250px; height:100vh; position: fixed; top: 0; left: 0; z-index: 1000;">
    
    {{-- 1. HEADER --}}
    <div class="p-4 border-bottom">
        <h4 class="fw-bold text-accent m-0">LaundryKita</h4>
        <p class="text-muted mb-0">Portal Karyawan</p>
    </div>

    {{-- 2. MENU LIST --}}
    {{-- Tambahkan 'flex-grow-1' agar list ini memanjang dan mendorong footer ke bawah --}}
    {{-- Tambahkan 'overflow-y-auto' agar menu bisa discroll jika terlalu panjang --}}
    <ul class="nav flex-column px-3 mt-4 flex-grow-1 overflow-y-auto">
        <li class="nav-item mb-2">
            <a href="{{ route('kasir.dashboard') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.dashboard') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-grid me-2"></i><span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="{{ route('kasir.pesanan.create') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pesanan.create') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-plus-lg me-2"></i><span>Pesanan Baru</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="{{ route('kasir.pesanan.index') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pesanan.index') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-file-text me-2"></i><span>Pesanan Aktif</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="{{ route('kasir.pembayaran.index') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.pembayaran.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-credit-card me-2"></i><span>Status Pembayaran</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="{{ route('kasir.history') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.history') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-clock-history me-2"></i><span>History Pesanan</span>
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="{{ route('kasir.profil.edit') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('kasir.profil.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-person me-2"></i><span>Profil</span>
            </a>
        </li>
    </ul>

    {{-- 3. TOMBOL KELUAR (FOOTER) --}}
    {{-- mt-auto memastikan dia menempel di bawah jika flex-grow di atas bekerja --}}
    <div class="p-3 mt-auto border-top">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </button>
        </form>
    </div>

</div>

@push('styles')
<style>
  /* Style warna sesuai request sebelumnya */
  .text-accent{color:#35C8B4!important;} 
  .bg-accent{background-color:#35C8B4!important;}
  
  /* Biar label menu rapi */
  .sidebar .nav-link{white-space:nowrap; display:flex; align-items:center;}
  .sidebar .nav-link i{flex:0 0 auto;}
  .sidebar .nav-link span{flex:1 1 auto;}
</style>
@endpush