@extends('adminlte::page')

@section('title', 'Layanan Digital (Pulsa & Token) - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-phone text-success me-1"></i> Layanan Digital: Pulsa & Token Listrik</h3>
            <small class="text-muted">
                @if ($pendingCount > 0)
                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>{{ $pendingCount }} antrean menunggu penyelesaian manual</span>
                @else
                    <span class="badge bg-success-subtle text-success-emphasis border"><i class="bi bi-check2-all me-1"></i>Semua antrean telah diproses</span>
                @endif
            </small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-cart3 me-1"></i> Transaksi Kasir Baru
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3 shadow-sm">
        <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
        <div>
            <strong>Catatan Operasional:</strong> {{ config('store.digital_manual_notice') }}
            Pembayaran di kasir mencatat pembelian pelanggan. Pemenuhan isi ulang pulsa / token dilakukan secara manual oleh petugas toko.
        </div>
    </div>
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('digital.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="q" class="form-label small fw-semibold mb-1">Cari Data</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" id="q" value="{{ $filters['q'] ?? '' }}" class="form-control"
                            placeholder="Nomor invoice, HP, ID pelanggan, meter, atau token">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label for="status" class="form-label small fw-semibold mb-1">Status Pemenuhan</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Diproses / Belum Selesai</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Layanan Selesai</option>
                        <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Layanan Gagal</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="service_type" class="form-label small fw-semibold mb-1">Jenis Layanan</label>
                    <select name="service_type" id="service_type" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="pulsa" @selected(($filters['service_type'] ?? '') === 'pulsa')>Pulsa HP</option>
                        <option value="token" @selected(($filters['service_type'] ?? '') === 'token')>Token Listrik PLN</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('digital.index') }}" class="btn btn-outline-secondary" title="Reset filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($services->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-phone display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada transaksi layanan digital yang cocok dengan filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 140px;">Invoice & Waktu</th>
                                <th scope="col" style="min-width: 180px;">Produk Layanan</th>
                                <th scope="col" style="min-width: 170px;">Target Tujuan</th>
                                <th scope="col" class="text-end">Nominal</th>
                                <th scope="col" class="text-center" style="min-width: 150px;">Status Pemenuhan</th>
                                <th scope="col" style="min-width: 200px;">Referensi / No. Token</th>
                                <th scope="col">Kasir</th>
                                <th scope="col" class="text-end" style="min-width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $service)
                                <tr>
                                    <td>
                                        @if ($service->saleItem?->sale)
                                            <a href="{{ route('sales.show', $service->saleItem->sale) }}" class="fw-bold">
                                                {{ $service->saleItem->sale->invoice_number }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        <div class="small text-muted">{{ $service->created_at->format('d/m/Y H:i') }} WIB</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $service->saleItem?->product_name ?? '-' }}</div>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border me-1">
                                                {{ $service->service_type === 'pulsa' ? 'Pulsa' : 'Token Listrik' }}
                                            </span>
                                            {{ $service->provider ?: '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($service->service_type === 'pulsa')
                                            <div class="small text-muted">No. HP:</div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="fw-bold font-monospace text-dark tabular-nums">{{ $service->phone_number ?? '-' }}</span>
                                                @if ($service->phone_number)
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-success copy-btn"
                                                        data-copy="{{ $service->phone_number }}" title="Salin Nomor HP">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            @if ($service->customer_id)
                                                <div class="small text-muted">ID Pelanggan:</div>
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <span class="fw-bold font-monospace tabular-nums">{{ $service->customer_id }}</span>
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-success copy-btn"
                                                        data-copy="{{ $service->customer_id }}" title="Salin ID Pelanggan">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                </div>
                                            @endif
                                            @if ($service->meter_number)
                                                <div class="small text-muted">No. Meter:</div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="fw-bold font-monospace tabular-nums">{{ $service->meter_number }}</span>
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-success copy-btn"
                                                        data-copy="{{ $service->meter_number }}" title="Salin No. Meter">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold tabular-nums">
                                        {{ \App\Support\Money::format($service->nominal) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($service->status->value === 'pending')
                                            <span class="badge badge-status-pending">
                                                <i class="bi bi-hourglass-split"></i> Belum Selesai
                                            </span>
                                        @elseif ($service->status->value === 'completed')
                                            <span class="badge badge-status-completed">
                                                <i class="bi bi-check2-all"></i> Selesai
                                            </span>
                                        @else
                                            <span class="badge badge-status-failed">
                                                <i class="bi bi-exclamation-octagon-fill"></i> Gagal
                                            </span>
                                        @endif

                                        @if ($service->status->value === 'failed' && $service->notes)
                                            <div class="small text-danger mt-1">{{ $service->notes }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($service->token_number)
                                            <div class="small text-muted">Nomor Token:</div>
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="font-monospace fw-bold text-dark px-2 py-1 bg-light border rounded tabular-nums">
                                                    {{ $service->token_number }}
                                                </span>
                                                <button type="button" class="btn btn-sm btn-link p-0 text-success copy-btn"
                                                    data-copy="{{ $service->token_number }}" title="Salin No. Token">
                                                    <i class="bi bi-copy"></i>
                                                </button>
                                            </div>
                                        @endif
                                        @if ($service->provider_reference)
                                            <div class="small text-muted mt-1">Ref: <span class="font-monospace">{{ $service->provider_reference }}</span></div>
                                        @endif
                                        @if (!$service->token_number && !$service->provider_reference)
                                            <span class="text-muted small">-</span>
                                        @endif

                                        @if ($service->completed_at)
                                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                                Diproses: {{ $service->completed_at->format('d/m H:i') }}
                                                @if ($service->completedBy) oleh {{ $service->completedBy->name }} @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="small">{{ $service->saleItem?->sale?->user?->name ?? '-' }}</td>
                                    <td class="text-end text-nowrap">
                                        @if ($service->status->value === 'pending')
                                            <button type="button" class="btn btn-sm btn-primary fw-semibold"
                                                data-bs-toggle="modal" data-bs-target="#digitalStatusModal"
                                                data-action="{{ route('digital.update', $service) }}"
                                                data-service-type="{{ $service->service_type }}"
                                                data-product-name="{{ $service->saleItem?->product_name ?? 'Layanan' }}"
                                                data-reference="{{ $service->provider_reference }}"
                                                data-token="{{ $service->token_number }}"
                                                data-notes="{{ $service->notes }}">
                                                <i class="bi bi-check2-circle me-1"></i> Proses
                                            </button>
                                        @else
                                            <span class="badge bg-body-secondary text-muted border">Final</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $services->firstItem() ?? 0 }} - {{ $services->lastItem() ?? 0 }} dari total {{ $services->total() }} pesanan digital
                    </div>
                    <div>
                        {{ $services->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade" id="digitalStatusModal" tabindex="-1" aria-labelledby="digitalStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="digital-status-form" class="modal-content shadow">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold" id="digitalStatusModalLabel">
                        <i class="bi bi-gear-fill me-1"></i> Proses Status Layanan Digital
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-circle-fill text-warning fs-5 flex-shrink-0"></i>
                        <div>
                            Pastikan pulsa atau token telah benar-benar dikirim/diterima sebelum menandai status <strong>Selesai</strong>.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal-status" class="form-label small fw-semibold">
                            Status Pemenuhan <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <select name="status" id="modal-status" class="form-select form-select-lg" required>
                            <option value="completed">Layanan Selesai (Pulsa/Token Terkirim)</option>
                            <option value="failed">Layanan Gagal (Gangguan / Nomor Keliru)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="modal-token-wrapper">
                        <label for="modal-token" class="form-label small fw-semibold">
                            Nomor Token Listrik (20 Digit)
                        </label>
                        <input type="text" name="token_number" id="modal-token" class="form-control font-monospace tabular-nums"
                            maxlength="100" placeholder="Contoh: 1234-5678-9012-3456-7890">
                        <div class="form-text">Nomor token akan dicetak pada struk pembeli.</div>
                    </div>

                    <div class="mb-3">
                        <label for="modal-reference" class="form-label small fw-semibold">
                            Nomor Referensi / Serial Number Provider (Opsional)
                        </label>
                        <input type="text" name="provider_reference" id="modal-reference" class="form-control font-monospace"
                            maxlength="100" placeholder="SN / ID Transaksi Provider">
                    </div>

                    <div class="mb-0">
                        <label for="modal-notes" class="form-label small fw-semibold">
                            Catatan Khusus <span class="text-danger d-none" id="notes-required-indicator">*</span>
                        </label>
                        <textarea name="notes" id="modal-notes" class="form-control" rows="2" maxlength="500"
                            placeholder="Wajib diisi jika layanan berstatus Gagal."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" id="modal-submit-btn">
                        <i class="bi bi-save me-1"></i> Simpan Status
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
    <script>
        (() => {
            const modal = document.getElementById('digitalStatusModal');
            if (modal) {
                const form = document.getElementById('digital-status-form');
                const reference = document.getElementById('modal-reference');
                const token = document.getElementById('modal-token');
                const tokenWrapper = document.getElementById('modal-token-wrapper');
                const notes = document.getElementById('modal-notes');
                const statusSelect = document.getElementById('modal-status');
                const notesIndicator = document.getElementById('notes-required-indicator');

                statusSelect?.addEventListener('change', () => {
                    const isFailed = statusSelect.value === 'failed';
                    notesIndicator?.classList.toggle('d-none', !isFailed);
                    if (isFailed) {
                        notes.setAttribute('required', 'required');
                        notes.focus();
                    } else {
                        notes.removeAttribute('required');
                    }
                });

                modal.addEventListener('show.bs.modal', (event) => {
                    const button = event.relatedTarget;
                    if (!button) return;

                    form.action = button.dataset.action || '';
                    reference.value = button.dataset.reference || '';
                    token.value = button.dataset.token || '';
                    notes.value = button.dataset.notes || '';
                    tokenWrapper.classList.toggle('d-none', button.dataset.serviceType !== 'token');

                    statusSelect.value = 'completed';
                    statusSelect.dispatchEvent(new Event('change'));
                });
            }

            document.querySelectorAll('.copy-btn').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const text = btn.dataset.copy;
                    if (!text) return;
                    try {
                        await navigator.clipboard.writeText(text);
                        const origHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                        btn.setAttribute('title', 'Tersalin!');
                        setTimeout(() => {
                            btn.innerHTML = origHtml;
                            btn.setAttribute('title', 'Salin');
                        }, 1800);
                    } catch (e) {
                        alert(`Nomor: ${text}`);
                    }
                });
            });
        })();
    </script>
@endpush
