@extends('adminlte::page')

@section('title', 'Kategori Barang - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-tags text-success me-1"></i> Kategori Barang</h3>
            <small class="text-muted">Kelola pengelompokan produk di katalog dan kasir.</small>
        </div>
        <a href="{{ route('categories.create') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="q" class="form-label small fw-semibold mb-1">Cari Kategori</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" id="q" value="{{ $search }}" class="form-control"
                            placeholder="Ketik nama kategori...">
                    </div>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                    @if ($search !== '')
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($categories->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-tags display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada kategori yang cocok.</p>
                    <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary mt-2">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Pertama
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 180px;">Nama Kategori</th>
                                <th scope="col">Deskripsi</th>
                                <th scope="col" class="text-center">Jumlah Barang</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end" style="min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $category->name }}</td>
                                    <td class="text-muted small">{{ $category->description ?: '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-body-secondary text-dark border tabular-nums">
                                            {{ $category->products_count }} produk
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($category->is_active)
                                            <span class="badge badge-status-safe">
                                                <i class="bi bi-check-circle-fill"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                <i class="bi bi-dash-circle"></i> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="table-actions">
                                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-primary"
                                                title="Ubah kategori {{ $category->name }}" aria-label="Ubah kategori">
                                                <i class="bi bi-pencil"></i> Ubah
                                            </a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Hapus kategori {{ $category->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus kategori {{ $category->name }}" aria-label="Hapus kategori">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari total {{ $categories->total() }} kategori
                    </div>
                    <div>
                        {{ $categories->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
