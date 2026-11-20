@extends('adminlte::page')

@section('title', 'Kelola Stok - ' . $product->name . ' - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-boxes text-success me-1"></i> Kelola Stok: {{ $product->name }}</h3>
            <small class="text-muted">SKU: {{ $product->sku }} &middot; Kategori: {{ $product->category?->name ?? 'Tanpa Kategori' }} &middot; Satuan: {{ $product->unit }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i> Ubah Data Barang
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash')

    @php($status = $product->stockStatus())
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-4">
            <div class="card shadow-sm text-center border-0 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Stok Tercatat Saat Ini</span>
                    <div class="display-5 fw-bold text-dark my-1 tabular-nums">{{ $product->stock }}</div>
                    <div class="text-muted small mb-2">{{ $product->unit }}</div>
                    @if ($product->stock <= 0)
                        <span class="badge badge-status-out"><i class="bi bi-x-octagon-fill"></i> Habis</span>
                    @elseif ($product->stock <= $product->min_stock)
                        <span class="badge badge-status-low"><i class="bi bi-exclamation-triangle-fill"></i> Menipis</span>
                    @else
                        <span class="badge badge-status-safe"><i class="bi bi-check-circle-fill"></i> Stok Aman</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card shadow-sm text-center border-0 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Batas Minimum Stok</span>
                    <div class="display-5 fw-bold text-dark my-1 tabular-nums">{{ $product->min_stock }}</div>
                    <div class="text-muted small">{{ $product->unit }}</div>
                    <div class="small text-muted mt-2">Peringatan otomatis saat stok &le; angka ini</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card shadow-sm text-center border-0 h-100 bg-white">
                <div class="card-body p-3">
                    <span class="text-muted small fw-semibold text-uppercase">Harga Jual & Modal</span>
                    <div class="display-6 fw-bold text-success my-1 tabular-nums">{{ \App\Support\Money::format($product->sell_price) }}</div>
                    <div class="text-muted small">Harga Modal: <span class="tabular-nums fw-semibold">{{ \App\Support\Money::format($product->cost_price) }}</span></div>
                    <div class="small text-muted mt-2">Margin per {{ $product->unit }}: {{ \App\Support\Money::format($product->sell_price - $product->cost_price) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success-subtle border-success-subtle py-2 px-3">
                    <h3 class="card-title fw-bold text-success-emphasis mb-0">
                        <i class="bi bi-plus-circle-fill me-1"></i> Penambahan Stok Masuk
                    </h3>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('products.stock.restock', $product) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="quantity" class="form-label small fw-semibold">
                                Jumlah Stok Masuk ({{ $product->unit }}) <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}"
                                class="form-control form-control-lg tabular-nums @error('quantity') is-invalid @enderror"
                                min="1" step="1" inputmode="numeric" placeholder="Contoh: 12" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Stok akan bertambah sebanyak angka yang dimasukkan.</div>
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label small fw-semibold">Catatan Transaksi Masuk</label>
                            <input type="text" name="note" id="note" value="{{ old('note') }}"
                                class="form-control @error('note') is-invalid @enderror" maxlength="255"
                                placeholder="Contoh: Pembelian dari Supplier Indomarco">
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Simpan Stok Masuk
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning-subtle border-warning-subtle py-2 px-3">
                    <h3 class="card-title fw-bold text-warning-emphasis mb-0">
                        <i class="bi bi-sliders me-1"></i> Penyesuaian Stok Fisik (Opname)
                    </h3>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('products.stock.adjust', $product) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="stock" class="form-label small fw-semibold">
                                Stok Riil Hasil Hitung Fisik ({{ $product->unit }}) <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}"
                                class="form-control form-control-lg tabular-nums @error('stock') is-invalid @enderror"
                                min="0" step="1" inputmode="numeric" required>
                            @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Nilai stok sistem akan diatur sama dengan angka ini.</div>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label small fw-semibold">
                                Alasan Penyesuaian Opname <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="text" name="reason" id="reason" value="{{ old('reason') }}"
                                class="form-control @error('reason') is-invalid @enderror" maxlength="255"
                                placeholder="Contoh: Selisih opname fisik / barang bocor / rusak" required>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-warning px-4 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Penyesuaian Opname
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
            <h3 class="card-title fw-bold mb-0">
                <i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Mutasi Barang Ini
            </h3>
            <a href="{{ route('stock-movements.index', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary">
                Lihat Seluruh Mutasi
            </a>
        </div>
        <div class="card-body p-0">
            @if ($movements->isEmpty())
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">Belum ada riwayat mutasi stok untuk barang ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Waktu</th>
                                <th scope="col">Jenis Mutasi</th>
                                <th scope="col" class="text-end">Perubahan</th>
                                <th scope="col" class="text-end">Sebelum</th>
                                <th scope="col" class="text-end">Sesudah</th>
                                <th scope="col">Dicatat Oleh</th>
                                <th scope="col">Catatan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr>
                                    <td class="small text-muted">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $movement->type->label() }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold tabular-nums {{ $movement->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }} {{ $product->unit }}
                                    </td>
                                    <td class="text-end tabular-nums text-muted small">{{ $movement->stock_before }}</td>
                                    <td class="text-end tabular-nums fw-semibold">{{ $movement->stock_after }}</td>
                                    <td class="small">{{ $movement->user?->name ?? 'Sistem' }}</td>
                                    <td class="small">{{ $movement->note ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
