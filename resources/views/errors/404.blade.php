@extends('adminlte::layouts.errors-master')

@section('title', '404 - Halaman Tidak Ditemukan | ' . config('store.name'))

@section('content')
    <div class="row justify-content-center text-center">
        <div class="col-md-7 col-lg-5">
            <div class="card p-4 shadow-sm border-0">
                <div class="mb-3 text-secondary">
                    <i class="bi bi-compass display-3 text-success"></i>
                </div>
                <h1 class="h3 fw-bold text-dark mb-2">Halaman Tidak Ditemukan (404)</h1>
                <p class="text-muted mb-4">
                    Halaman yang Anda tuju tidak tersedia atau tautan yang dimasukkan keliru. Periksa kembali alamat halaman atau kembali ke beranda.
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
