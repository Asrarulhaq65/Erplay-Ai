@extends('layouts.app')

@section('title', 'Langganan Berakhir')

@section('content')
<div class="container-fluid px-4 py-5 d-flex justify-content-center">
    <div class="card card-erp text-center" style="max-width: 500px; width: 100%; border-top: 4px solid #dc3545;">
        <div class="card-body p-5">
            <div class="mb-4 text-danger">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 4rem;"></i>
            </div>
            <h3 class="fw-bold text-dark mb-3">Akses POS Diblokir</h3>
            
            @if(session('error'))
                <p class="text-muted mb-4">{{ session('error') }}</p>
            @else
                <p class="text-muted mb-4">
                    Mohon maaf, masa berlangganan toko Anda telah berakhir atau dinonaktifkan. Anda tidak dapat mengakses sistem kasir (POS) saat ini.
                </p>
            @endif

            <div class="bg-light p-3 rounded mb-4 text-start" style="font-size: 13px;">
                <strong>Informasi Toko:</strong><br>
                <span class="text-muted">Nama Toko:</span> {{ auth()->user()->toko->nama_toko }}<br>
                <span class="text-muted">Status:</span> <span class="badge bg-danger">{{ auth()->user()->toko->status_langganan }}</span><br>
                @if(auth()->user()->toko->berakhir_pada)
                <span class="text-muted">Kedaluwarsa Sejak:</span> {{ auth()->user()->toko->berakhir_pada->format('d M Y') }}
                @endif
            </div>

            <p class="text-muted mb-4" style="font-size: 13px;">
                Untuk kembali menggunakan layanan POS, silakan hubungi tim *Support* atau Administrator sistem kami untuk memperpanjang masa langganan Anda.
            </p>

            <a href="{{ url('/dashboard') }}" class="btn btn-pb w-100">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
