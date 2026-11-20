@extends('adminlte::layouts.errors-master')

@section('title', '500 - Kendala Sistem | ' . config('store.name'))

@section('content')
    <div class="row justify-content-center text-center">
        <div class="col-md-7 col-lg-5">
            <div class="card p-4 shadow-sm border-0">
                <div class="mb-3 text-danger">
                    <i class="bi bi-exclamation-octagon-fill display-3"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-2">Terjadi Kendala Sistem (500)</h1>
                <p class="text-muted mb-4">
                    Sistem toko sedang mengalami kendala sementara saat memproses permintaan Anda. Data transaksi tetap terlindungi.
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                        <i class="bi bi-house-door me-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
