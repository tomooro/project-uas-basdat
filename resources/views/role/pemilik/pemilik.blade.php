@extends('layouts.app')

@section('title', 'Dashboard Pemilik - LaundryKita')

@section('content')
<div class="d-flex">

    <!-- Konten Utama -->
    <div class="flex-grow-1 p-4" style="background: #f1f5f4ff;">
        <h2 class="fw-bold mb-3">Ringkasan Bisnis</h2>
        <p class="text-muted">Selamat datang kembali, Pemilik! Berikut performa bisnis laundry kamu.</p>

        <!-- Kartu Statistik -->
        <div class="row mb-4">
            @php
                $cardColor = 'primary';
            @endphp

            <!-- Pendapatan Hari Ini -->
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pendapatan Hari Ini</h6>
                            <h4 class="fw-bold text-primary">
                                Rp{{ number_format($pendapatanHariIni ?? 0, 0, ',', '.') }}
                            </h4>
                        </div>
                        <div class="icon-circle bg-primary-soft text-primary">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesanan Hari Ini -->
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pesanan Hari Ini</h6>
                            <h4 class="fw-bold text-primary">{{ $pesananHariIni ?? 0 }}</h4>
                        </div>
                        <div class="icon-circle bg-primary-soft text-primary">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesanan Aktif -->
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pesanan Aktif</h6>
                            <h4 class="fw-bold text-primary">{{ $pesananAktif ?? 0 }}</h4>
                            <small class="text-muted">Sedang diproses</small>
                        </div>
                        <div class="icon-circle bg-primary-soft text-primary">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Karyawan Aktif -->
            <div class="col-md-3">
                <div class="card stat-card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Karyawan Aktif</h6>
                            <h4 class="fw-bold text-primary">{{ $karyawanAktif ?? 0 }}</h4>
                            <small class="text-muted">Sedang bekerja</small>
                        </div>
                        <div class="icon-circle bg-primary-soft text-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan & Layanan -->
        <div class="row">
            <!-- Ringkasan Pendapatan -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-accent">Ringkasan Pendapatan</h6>
                        <p class="text-muted">Detail pendapatan berdasarkan periode waktu</p>
                        <div class="d-flex justify-content-between text-center">
                            <div>
                                <h5 class="fw-bold text-primary">
                                    Rp{{ number_format(($ringkasan['hari'] ?? 0), 0, ',', '.') }}
                                </h5>
                                <small>Hari Ini</small>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary">
                                    Rp{{ number_format(($ringkasan['minggu'] ?? 0), 0, ',', '.') }}
                                </h5>
                                <small>Minggu Ini</small>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary">
                                    Rp{{ number_format(($ringkasan['bulan'] ?? 0), 0, ',', '.') }}
                                </h5>
                                <small>Bulan Ini</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layanan Terpopuler -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-accent">Layanan Terpopuler</h6>
                        @php $rank = 1; @endphp
                        @forelse(($layananTop ?? []) as $row)
                            <p class="mb-1">
                                {{ $rank++ }}. {{ $row['nama'] }}
                                <span class="fw-semibold text-accent">
                                    {{ $row['pesanan'] }} pesanan
                                </span>
                            </p>
                        @empty
                            <p class="mb-1 text-muted">Belum ada layanan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-card {
        transition: 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-4px);
    }

    .icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .bg-primary-soft {
        background: rgba(13, 110, 253, 0.15);
    }

    .text-accent {
        color: #35C8B4;
    }
</style>
@endpush
