@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert" aria-live="polite">
        <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
        <div class="flex-grow-1">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert" aria-live="assertive">
        <i class="bi bi-exclamation-octagon-fill fs-5 text-danger flex-shrink-0"></i>
        <div class="flex-grow-1">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert" aria-live="polite">
        <i class="bi bi-info-circle-fill fs-5 text-info flex-shrink-0"></i>
        <div class="flex-grow-1">{{ session('info') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm" role="alert" aria-live="assertive">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
            <strong class="fw-semibold">Mohon periksa data formulir yang belum sesuai:</strong>
        </div>
        <ul class="mb-0 ps-4 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif
