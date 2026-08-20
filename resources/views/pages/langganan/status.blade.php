@extends('layouts.app')

@section('title', 'Status Langganan')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1" style="font-weight: 700; color: var(--pb-text);">
                <i class="bi bi-shield-check me-2 text-primary"></i>Status Langganan
            </h4>
            <p class="text-muted mb-0" style="font-size: 13px;">Informasi paket berlangganan toko Anda.</p>
        </div>
    </div>

    @php
        $toko = auth()->user()->toko;
    @endphp

    <div class="row">
        <div class="col-md-6">
            <div class="card card-erp h-100">
                <div class="card-header bg-white pb-0 border-0 pt-4 px-4">
                    <h6 class="card-title mb-0">Informasi Paket</h6>
                </div>
                <div class="card-body px-4 pt-3 pb-4">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-muted" style="width: 150px;">Nama Toko</td>
                            <td class="fw-bold">{{ $toko->nama_toko }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Langganan</td>
                            <td>
                                @if($toko->status_langganan === 'Aktif')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                @elseif($toko->status_langganan === 'Trial')
                                    <span class="badge bg-info text-dark"><i class="bi bi-clock-history me-1"></i>Trial</span>
                                @elseif($toko->status_langganan === 'Kedaluwarsa')
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Kedaluwarsa</span>
                                @else
                                    <span class="badge bg-dark">{{ $toko->status_langganan }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Berakhir Pada</td>
                            <td>
                                @if($toko->berakhir_pada)
                                    <span class="{{ $toko->berakhir_pada->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ $toko->berakhir_pada->format('d M Y') }}
                                        @if($toko->berakhir_pada->isPast())
                                            (Telah Berlalu)
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">Selamanya (Akses Penuh)</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($toko->status_langganan === 'Kedaluwarsa' || ($toko->berakhir_pada && $toko->berakhir_pada->isPast()))
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Akses sistem kasir (POS) saat ini ditangguhkan. Hubungi Administrator untuk perpanjangan.
                        </div>
                    @else
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i> Langganan Anda aktif. Semua fitur berjalan normal.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mt-4 mt-md-0">
            <div class="card card-erp h-100">
                <div class="card-header bg-white pb-0 border-0 pt-4 px-4">
                    <h6 class="card-title mb-0">Bantuan & Tagihan</h6>
                </div>
                <div class="card-body px-4 pt-3 pb-4 d-flex flex-column justify-content-center text-center">
                    <i class="bi bi-headset text-muted mb-3" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-4">Jika Anda butuh perpanjangan kuota kasir, penambahan pengguna, atau pembayaran tagihan, silakan hubungi tim dukungan kami.</p>
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-whatsapp me-2"></i> Hubungi Dukungan via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
