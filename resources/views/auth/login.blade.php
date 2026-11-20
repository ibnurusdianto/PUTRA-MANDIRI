@extends('adminlte::auth.auth-master', ['authType' => 'login'])

@section('title', 'Masuk - ' . config('store.name'))

@section('auth_body')
    <div class="text-center mb-4">
        <h5 class="fw-bold mb-1 text-dark">Masuk ke Sistem</h5>
        <p class="text-muted small mb-0">Kelola transaksi kasir dan operasional toko 24 jam</p>
    </div>

    @include('partials.flash')

    <form action="{{ route('login.store') }}" method="post" autocomplete="on" id="login-form">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold small">Email Pengguna <span class="text-danger" aria-hidden="true">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="nama@putramandiri.test" required autofocus autocomplete="username"
                       inputmode="email">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Password <span class="text-danger" aria-hidden="true">*</span></label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Masukkan password" required autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" id="toggle-password" aria-label="Tampilkan password" aria-pressed="false">
                    <i class="bi bi-eye" id="toggle-password-icon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="form-check">
                <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember"
                    @checked(old('remember'))>
                <label class="form-check-label small user-select-none" for="remember">Ingat saya</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-semibold" id="login-submit">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
        </button>
    </form>

    <div class="border-top pt-3 mt-4 text-center">
        <p class="text-muted small mb-0">
            <i class="bi bi-shield-lock me-1"></i> Registrasi publik tidak dibuka. Akses kasir dan owner dibuat melalui Owner.
        </p>
    </div>
@endsection

@push('js')
<script>
    (() => {
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggle-password-icon');
        const loginForm = document.getElementById('login-form');
        const submitBtn = document.getElementById('login-submit');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
                toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memeriksa...';
            });
        }
    })();
</script>
@endpush
