<div class="sidebar d-flex flex-column border-end bg-white">
    
    {{-- Header --}}
    <div class="p-4 border-bottom">
        <h4 class="fw-bold text-accent mb-0">LaundryKita</h4>
        <small class="text-muted">Portal Pemilik</small>
    </div>

    {{-- Menu List --}}
    <ul class="nav flex-column px-3 mt-3 flex-grow-1">

        {{-- Dashboard --}}
        <li class="nav-item mb-2">
            <a href="{{ url('/pemilik') }}"
               class="nav-link px-3 py-2 {{ request()->is('pemilik') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-grid me-2"></i> Dashboard
            </a>
        </li>

        {{-- Cabang --}}
        <li class="nav-item mb-2">
            <a href="{{ route('pemilik.cabang.index') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.cabang.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-shop me-2"></i> Cabang
            </a>
        </li>

        {{-- Karyawan --}}
        <li class="nav-item mb-2">
            <a href="{{ route('pemilik.karyawan.index') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.karyawan.*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-people me-2"></i> Karyawan
            </a>
        </li>

        {{-- Layanan (Opsional, pastikan route ada) --}}
        <li class="nav-item mb-2">
             {{-- Kalau route layanan belum ada, ganti href="#" dulu biar gak error --}}
            <a href="{{ url('/pemilik/layanan') }}"
               class="nav-link px-3 py-2 {{ request()->is('pemilik/layanan*') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-gear me-2"></i> Layanan
            </a>
        </li>

        {{-- History --}}
        <li class="nav-item mb-2">
            <a href="{{ route('pemilik.history') }}"
               class="nav-link px-3 py-2 {{ request()->routeIs('pemilik.history') ? 'active fw-bold text-white rounded bg-accent' : 'text-dark' }}">
                <i class="bi bi-clock-history me-2"></i> History
            </a>
        </li>

    </ul>

    {{-- Footer / Logout --}}
    <div class="p-3 border-top mt-auto">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </button>
        </form>
    </div>
</div>