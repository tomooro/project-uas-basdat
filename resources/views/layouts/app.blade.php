<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LaundryKita')</title>

    <link rel="icon" href="{{ asset('images/no_background.png') }}" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* === WARNA TEMA === */
        .text-accent { color: #35C8B4 !important; }
        .bg-accent   { background-color: #35C8B4 !important; }

        /* === SIDEBAR STYLE (FIXED) === */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            background-color: #ffffff;
            border-right: 1px solid #eaeaea;
        }

        /* === MAIN CONTENT (Khusus User Login) === */
        .main-content {
            margin-left: 250px;     /* Geser kanan 250px */
            width: auto;
            min-height: 100vh;      
            background-color: #f1f5f4;
            padding-top: 1px;
        }
        
        /* === GUEST CONTENT (Khusus Halaman Login) === */
        .guest-content {
            margin-left: 0;         /* Full width */
            width: 100%;
            min-height: 100vh;
            background-color: #fff; /* Atau warna lain untuk login page */
        }

        /* === NAV LINK STYLE === */
        .nav-link {
            color: #555;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .nav-link i { font-size: 1.1rem; }

        /* State Active */
        .nav-link.active-accent {
            background-color: #35C8B4 !important;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(53, 200, 180, 0.3);
        }

        /* State Hover */
        .nav-link:hover:not(.active-accent) {
            background-color: #e0f7fa;
            color: #35C8B4;
        }

        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .sidebar { left: -250px; }
            .main-content { margin-left: 0; }
        }
    </style>
    
    @stack('styles')
</head>
<body>

    {{-- LOGIKA PENTING DISINI --}}
    
    @auth
        {{-- === JIKA SUDAH LOGIN === --}}
        
        {{-- 1. Tampilkan Sidebar --}}
        @include('role.pemilik.partials.sidebar-pemilik')

        {{-- 2. Tampilkan Konten dengan Margin Kiri (main-content) --}}
        <main class="main-content">
            <div class="p-4">
                @yield('content')
            </div>
        </main>

    @else
        {{-- === JIKA BELUM LOGIN (GUEST) === --}}
        
        {{-- Tampilkan Konten Full Width (guest-content) tanpa Sidebar --}}
        <main class="guest-content d-flex align-items-center justify-content-center">
            <div class="w-100">
                @yield('content')
            </div>
        </main>
        
    @endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>