@php($editing = $expense->exists)

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="expense_date" class="form-label small fw-semibold">
            Tanggal Pengeluaran <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input type="date" name="expense_date" id="expense_date"
            value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
            class="form-control form-control-lg @error('expense_date') is-invalid @enderror" required>
        @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="category" class="form-label small fw-semibold">
            Kategori / Jenis Biaya <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input type="text" name="category" id="category" value="{{ old('category', $expense->category) }}"
            class="form-control form-control-lg @error('category') is-invalid @enderror" maxlength="50" list="expense-categories"
            placeholder="Listrik, Air, Gaji, Sewa, Plastik, dll." required>
        <datalist id="expense-categories">
            @foreach ($categories ?? [] as $expenseCategory)
                <option value="{{ $expenseCategory }}"></option>
            @endforeach
        </datalist>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="amount" class="form-label small fw-semibold">
            Nominal Pengeluaran <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <div class="input-group">
            <span class="input-group-text bg-body-secondary">Rp</span>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}"
                class="form-control form-control-lg tabular-nums @error('amount') is-invalid @enderror"
                min="1" step="1" inputmode="numeric" placeholder="100000" required>
        </div>
        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label small fw-semibold">Keterangan Tambahan (Opsional)</label>
        <textarea name="description" id="description" rows="2" maxlength="500"
            class="form-control @error('description') is-invalid @enderror"
            placeholder="Rincian pengeluaran, nomor tagihan token listrik toko, atau penerima pembayaran">{{ old('description', $expense->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mt-3 mb-0">
    <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
    <div>
        Biaya operasional toko diperhitungkan saat menghitung <strong>Laba Bersih Toko</strong> pada Laporan Laba Rugi. Pembelian persediaan barang dagangan dihitung terpisah via HPP transaksi.
    </div>
</div>

<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Simpan Perubahan Biaya' : 'Simpan Catatan Pengeluaran' }}
    </button>
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary px-3 py-2">
        Batal
    </a>
</div>
