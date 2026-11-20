@extends('adminlte::page')

@section('title', 'Katalog Barang - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-box-seam text-success me-1"></i> Katalog Barang</h3>
            <small class="text-muted">Kelola master data barang fisik, produk digital, harga, dan batas stok.</small>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Tambah Barang
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="q" class="form-label small fw-semibold mb-1">Cari Nama / SKU / Barcode</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                            placeholder="Ketik pencarian...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="category_id" class="form-label small fw-semibold mb-1">Kategori</label>
                    <select name="category_id" id="category_id" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="type" class="form-label small fw-semibold mb-1">Jenis Produk</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(($filters['type'] ?? '') === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label small fw-semibold mb-1">Status / Stok</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
                        <option value="low" @selected(($filters['status'] ?? '') === 'low')>Stok Menipis</option>
                        <option value="out" @selected(($filters['status'] ?? '') === 'out')>Stok Habis</option>
                    </select>
                </div>
                <div class="col-6 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-outline-primary w-100" title="Terapkan Filter" aria-label="Terapkan Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset Filter" aria-label="Reset Filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($products->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-box-seam display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada barang yang cocok dengan filter.</p>
                    <p class="small text-muted mb-3">Sesuaikan kata kunci atau tambahkan produk baru.</p>
                    <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Barang Baru
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 220px;">Barang & SKU</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Jenis</th>
                                <th scope="col" class="text-end">Harga Modal</th>
                                <th scope="col" class="text-end">Harga Jual</th>
                                <th scope="col" class="text-center">Kondisi Stok</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col" class="text-end" style="min-width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php($stock = $product->stockStatus())
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                        <div class="small text-muted">
                                            <span class="me-2"><i class="bi bi-upc me-1"></i>SKU: {{ $product->sku }}</span>
                                            @if ($product->barcode)
                                                <span><i class="bi bi-barcode me-1"></i>{{ $product->barcode }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-body-secondary text-dark border">
                                            {{ $product->category?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $product->type->isPhysical() ? 'bg-light text-dark border' : 'bg-info-subtle text-info-emphasis border border-info-subtle' }}">
                                            <i class="bi {{ $product->type->isPhysical() ? 'bi-box' : 'bi-phone' }} me-1"></i>
                                            {{ $product->type->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end text-muted small tabular-nums">
                                        {{ \App\Support\Money::format($product->cost_price) }}
                                    </td>
                                    <td class="text-end fw-bold text-success tabular-nums">
                                        {{ \App\Support\Money::format($product->sell_price) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($product->type->isPhysical())
                                            @if ($product->stock <= 0)
                                                <span class="badge badge-status-out">
                                                    <i class="bi bi-x-octagon-fill"></i> Habis
                                                </span>
                                            @elseif ($product->stock <= $product->min_stock)
                                                <span class="badge badge-status-low">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Sisa {{ $product->stock }} {{ $product->unit }}
                                                </span>
                                            @else
                                                <span class="badge badge-status-safe">
                                                    <i class="bi bi-check-circle-fill"></i> {{ $product->stock }} {{ $product->unit }}
                                                </span>
                                            @endif
                                            <div class="text-muted" style="font-size: 0.72rem;">Min: {{ $product->min_stock }} {{ $product->unit }}</div>
                                        @else
                                            <span class="badge bg-light text-muted border">Layanan Digital</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($product->is_active)
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
                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary"
                                                title="Ubah data {{ $product->name }}" aria-label="Ubah data">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if ($product->type->isPhysical())
                                                <a href="{{ route('products.stock', $product) }}" class="btn btn-sm btn-outline-success"
                                                    title="Kelola & riwayat stok" aria-label="Kelola stok">
                                                    <i class="bi bi-boxes"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Nonaktifkan / hapus barang {{ $product->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus / Nonaktifkan {{ $product->name }}" aria-label="Hapus barang">
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
                        Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari total {{ $products->total() }} barang
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
