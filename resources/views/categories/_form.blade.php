@php($editing = $category->exists)

<div class="row g-3">
    <div class="col-12 col-md-5">
        <label for="name" class="form-label small fw-semibold">
            Nama Kategori <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}"
            class="form-control form-control-lg @error('name') is-invalid @enderror" maxlength="100"
            placeholder="Contoh: Makanan Ringan, Minuman Dingin, Sembako" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-5">
        <label for="description" class="form-label small fw-semibold">Deskripsi (Opsional)</label>
        <input type="text" name="description" id="description" value="{{ old('description', $category->description) }}"
            class="form-control form-control-lg @error('description') is-invalid @enderror" maxlength="255"
            placeholder="Penjelasan singkat pengelompokan barang">
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-2">
        <label class="form-label small fw-semibold d-block">Status</label>
        <div class="form-check form-switch mt-2">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                @checked(old('is_active', $category->is_active ?? true))>
            <label for="is_active" class="form-check-label fw-semibold">Aktif</label>
        </div>
    </div>
</div>

<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Simpan Perubahan Kategori' : 'Simpan Kategori Baru' }}
    </button>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary px-3 py-2">
        Batal
    </a>
</div>
