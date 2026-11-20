@extends('adminlte::page')

@section('title', 'Riwayat Mutasi Stok - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right text-success me-1"></i> Riwayat Mutasi Stok</h3>
            <small class="text-muted">Audit perubahan stok otomatis dari penjualan kasir, stok awal, dan penyesuaian opname.</small>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-box-seam me-1"></i> Katalog Barang
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('stock-movements.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="product_id" class="form-label small fw-semibold mb-1">Barang</label>
                    <select name="product_id" id="product_id" class="form-select">
                        <option value="">Semua Barang</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((int) ($filters['product_id'] ?? 0) === $product->id)>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="type" class="form-label small fw-semibold mb-1">Jenis Mutasi</label>
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
                    <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-md-2">
                    <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary" title="Reset filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($movements->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-arrow-left-right display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-0 fw-semibold text-dark">Belum ada mutasi stok yang cocok dengan filter.</p>
                    <small class="text-muted">Coba ubah rentang tanggal atau pilih barang lain.</small>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 140px;">Waktu Mutasi</th>
                                <th scope="col" style="min-width: 200px;">Barang & SKU</th>
                                <th scope="col">Jenis Mutasi</th>
                                <th scope="col" class="text-end">Perubahan</th>
                                <th scope="col" class="text-end">Sebelum</th>
                                <th scope="col" class="text-end">Sesudah</th>
                                <th scope="col">Referensi</th>
                                <th scope="col">Operator</th>
                                <th scope="col">Catatan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="text-nowrap small text-muted">
                                        {{ $movement->created_at->format('d/m/Y H:i') }} WIB
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $movement->product?->name ?? 'Barang Terhapus' }}</div>
                                        <small class="text-muted"><i class="bi bi-upc me-1"></i>{{ $movement->product?->sku ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-body-secondary text-dark border">
                                            {{ $movement->type->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold tabular-nums {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td class="text-end tabular-nums text-muted small">{{ $movement->stock_before }}</td>
                                    <td class="text-end tabular-nums fw-semibold">{{ $movement->stock_after }}</td>
                                    <td>
                                        @if ($movement->sale)
                                            <a href="{{ route('sales.show', $movement->sale) }}" class="fw-semibold">
                                                {{ $movement->sale->invoice_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $movement->user?->name ?? 'Sistem' }}</td>
                                    <td class="small text-muted">{{ $movement->note ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} dari total {{ $movements->total() }} catatan mutasi
                    </div>
                    <div>
                        {{ $movements->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
