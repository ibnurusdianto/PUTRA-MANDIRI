@php($editing = $user->exists)

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="name" class="form-label small fw-semibold">
            Nama Lengkap <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
            class="form-control form-control-lg @error('name') is-invalid @enderror" maxlength="100"
            placeholder="Contoh: Muhammad Hardiansah" required autofocus>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="email" class="form-label small fw-semibold">
            Alamat Email Login <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
            class="form-control form-control-lg @error('email') is-invalid @enderror" maxlength="255"
            placeholder="nama@putramandiri.test" inputmode="email" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="role" class="form-label small fw-semibold">
            Peran / Hak Akses <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <select name="role" id="role" class="form-select form-select-lg @error('role') is-invalid @enderror" required
            @disabled($editing && auth()->id() === $user->id)>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role?->value ?? 'kasir') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @if ($editing && auth()->id() === $user->id)
            <input type="hidden" name="role" value="{{ $user->role->value }}">
            <div class="form-text text-warning"><i class="bi bi-shield-lock me-1"></i>Peran akun sendiri tidak dapat diubah.</div>
        @endif
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="password" class="form-label small fw-semibold">
            Password {{ $editing ? '(Kosongkan jika tidak diubah)' : '' }}
            @unless ($editing)<span class="text-danger" aria-hidden="true">*</span>@endunless
        </label>
        <input type="password" name="password" id="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
            minlength="8" autocomplete="new-password" @required(!$editing) placeholder="{{ $editing ? '••••••••' : 'Minimal 8 karakter' }}">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg"
            minlength="8" autocomplete="new-password" @required(!$editing) placeholder="{{ $editing ? '••••••••' : 'Ulangi password' }}">
    </div>
</div>

<div class="alert alert-secondary py-2 px-3 small d-flex align-items-center gap-2 mt-3 mb-0">
    <i class="bi bi-shield-check text-success fs-5 flex-shrink-0"></i>
    <div>
        <strong>Role Kasir</strong> memiliki akses ke POS, input penjualan pulsa/token, riwayat transaksi, dan cetak struk.
        <strong>Role Owner</strong> memiliki akses penuh termasuk laporan keuangan, katalog barang, opname stok, pengeluaran, dan manajemen akun.
    </div>
</div>

<div class="mt-4 pt-3 border-top d-flex flex-wrap gap-2">
    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> {{ $editing ? 'Simpan Perubahan Akun' : 'Simpan Akun Pengguna' }}
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-3 py-2">
        Batal
    </a>
</div>
