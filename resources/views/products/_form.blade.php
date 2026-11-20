@php($editing = $product->exists)
<div class="mb-4">
    <h5 class="fw-bold border-bottom pb-2 text-dark">
        <i class="bi bi-info-circle text-success me-1"></i> 1. Informasi Dasar Produk
    </h5>
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="category_id" class="form-label small fw-semibold">
                Kategori <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="sku" class="form-label small fw-semibold">
                Kode Barang / SKU <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}"
                class="form-control @error('sku') is-invalid @enderror" maxlength="50" placeholder="Contoh: ATK-001" required>
            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="barcode" class="form-label small fw-semibold">
                Barcode (Opsional)
            </label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary"><i class="bi bi-upc"></i></span>
                <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}"
                    class="form-control @error('barcode') is-invalid @enderror" maxlength="64" placeholder="Scan atau ketik barcode"
                    inputmode="numeric">
            </div>
            @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-8">
            <label for="name" class="form-label small fw-semibold">
                Nama Barang <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}"
                class="form-control @error('name') is-invalid @enderror" maxlength="150" placeholder="Contoh: Beras Pandan Wangi 5 kg" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="unit" class="form-label small fw-semibold">
                Satuan Kemasan <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <input type="text" name="unit" id="unit" value="{{ old('unit', $product->unit) }}"
                class="form-control @error('unit') is-invalid @enderror" maxlength="30" placeholder="pcs, botol, dus, bungkus" required>
            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="mb-4">
    <h5 class="fw-bold border-bottom pb-2 text-dark">
        <i class="bi bi-tag text-success me-1"></i> 2. Jenis Produk & Layanan
    </h5>
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="product_type" class="form-label small fw-semibold">
                Jenis Produk <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <select name="type" id="product_type" class="form-select @error('type') is-invalid @enderror" required>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $product->type?->value ?? 'physical') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-4 digital-field">
            <label for="provider" class="form-label small fw-semibold">
                Provider / Operator
            </label>
            <input type="text" name="provider" id="provider" value="{{ old('provider', $product->provider) }}"
                class="form-control @error('provider') is-invalid @enderror" maxlength="50" placeholder="Telkomsel, PLN, Indosat, dll.">
            @error('provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12 col-md-4 digital-field">
            <label for="nominal" class="form-label small fw-semibold">
                Nominal Layanan
            </label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">Rp</span>
                <input type="number" name="nominal" id="nominal" value="{{ old('nominal', $product->nominal) }}"
                    class="form-control @error('nominal') is-invalid @enderror" min="0" step="1" placeholder="10000" inputmode="numeric">
            </div>
            @error('nominal')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="mb-4">
    <h5 class="fw-bold border-bottom pb-2 text-dark">
        <i class="bi bi-cash-stack text-success me-1"></i> 3. Harga Jual & Batas Stok
    </h5>
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="cost_price" class="form-label small fw-semibold">
                Harga Modal (HPP) <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">Rp</span>
                <input type="number" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
                    class="form-control tabular-nums @error('cost_price') is-invalid @enderror" min="0" step="1" inputmode="numeric" required>
            </div>
            @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Dipakai untuk menghitung laba kotor toko.</div>
        </div>

        <div class="col-12 col-md-4">
            <label for="sell_price" class="form-label small fw-semibold">
                Harga Jual ke Pelanggan <span class="text-danger" aria-hidden="true">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary">Rp</span>
                <input type="number" name="sell_price" id="sell_price" value="{{ old('sell_price', $product->sell_price) }}"
                    class="form-control tabular-nums @error('sell_price') is-invalid @enderror" min="0" step="1" inputmode="numeric" required>
            </div>
            @error('sell_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Harga default saat di-scan di kasir (POS).</div>
        </div>

        <div class="col-12 col-md-4 physical-field">
            <label for="min_stock" class="form-label small fw-semibold">
                Batas Minimum Stok
            </label>
            <input type="number" name="min_stock" id="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}"
                class="form-control tabular-nums @error('min_stock') is-invalid @enderror" min="0" step="1" inputmode="numeric">
            @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Peringatan stok menipis muncul jika stok &le; angka ini.</div>
        </div>

        @unless ($editing)
            <div class="col-12 col-md-4 physical-field">
                <label for="stock" class="form-label small fw-semibold">
                    Stok Awal Fisik
                </label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock ?? 0) }}"
                    class="form-control tabular-nums @error('stock') is-invalid @enderror" min="0" step="1" inputmode="numeric">
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Akan dicatat otomatis sebagai mutasi stok awal.</div>
            </div>
        @endunless

        @if ($editing && $product->type->isPhysical())
            <input type="hidden" name="stock" value="{{ $product->stock }}">
            <div class="col-12">
                <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
                    <div>
                        Stok tercatat saat ini: <strong>{{ $product->stock }} {{ $product->unit }}</strong>.
                        Untuk menambah stok masuk atau opname fisik, gunakan
                        <a href="{{ route('products.stock', $product) }}" class="alert-link text-decoration-underline">Halaman Kelola Stok</a>.
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="mb-4">
    <h5 class="fw-bold border-bottom pb-2 text-dark">
        <i class="bi bi-gear text-success me-1"></i> 4. Status & Foto Barang
    </h5>
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="form-label small fw-semibold d-block">Status Ketersediaan</label>
            <div class="form-check form-switch mt-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                    @checked(old('is_active', $product->is_active ?? true))>
                <label for="is_active" class="form-check-label fw-semibold">
                    Aktif dan dapat dicari di Kasir (POS)
                </label>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label for="image" class="form-label small fw-semibold">Foto Barang (Opsional)</label>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                class="form-control @error('image') is-invalid @enderror">
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @if ($product->image_path)
                <div class="form-text mt-1">
                    <i class="bi bi-image me-1"></i>
                    Foto terpasang:
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" target="_blank" rel="noopener" class="text-success">
                        Lihat foto saat ini
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Simpan Perubahan' : 'Simpan Barang Baru' }}
    </button>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-3 py-2">
        Batal
    </a>
</div>
