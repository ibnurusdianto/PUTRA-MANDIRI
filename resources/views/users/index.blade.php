@extends('adminlte::page')

@section('title', 'Manajemen Pengguna - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-people text-success me-1"></i> Manajemen Pengguna & Hak Akses</h3>
            <small class="text-muted">Kelola akun Owner dan Kasir toko PUTRA MANDIRI.</small>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="q" class="form-label small fw-semibold mb-1">Cari Nama / Email Pengguna</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" id="q" value="{{ $search }}" class="form-control"
                            placeholder="Ketik nama atau email...">
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada pengguna yang cocok.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 180px;">Nama Pengguna</th>
                                <th scope="col">Alamat Email</th>
                                <th scope="col" class="text-center">Peran (Role)</th>
                                <th scope="col">Tanggal Dibuat</th>
                                <th scope="col" class="text-end" style="min-width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                        @if (auth()->id() === $user->id)
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle small">
                                                <i class="bi bi-person-check-fill me-1"></i> Akun Anda
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($user->isOwner())
                                            <span class="badge bg-primary text-white">
                                                <i class="bi bi-shield-check me-1"></i> {{ $user->role->label() }}
                                            </span>
                                        @else
                                            <span class="badge bg-success text-white">
                                                <i class="bi bi-cart3 me-1"></i> {{ $user->role->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $user->created_at->format('d/m/Y') }}</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary"
                                            title="Ubah data pengguna {{ $user->name }}" aria-label="Ubah data">
                                            <i class="bi bi-pencil me-1"></i> Ubah
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari total {{ $users->total() }} akun
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
