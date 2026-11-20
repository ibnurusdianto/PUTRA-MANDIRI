@extends('adminlte::page')

@section('title', 'Detail Transaksi ' . $sale->invoice_number . ' - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h3 class="mb-0 fw-bold"><i class="bi bi-receipt text-success me-1"></i> Transaksi {{ $sale->invoice_number }}</h3>
                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                    <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Tercatat
                </span>
            </div>
            <small class="text-muted">{{ $sale->created_at->translatedFormat('l, d F Y H:i') }} WIB &middot; Kasir: {{ $sale->user?->name ?? 'Kasir Toko' }}</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-primary fw-semibold">
                <i class="bi bi-printer me-1"></i> Cetak Struk
            </a>
            <a href="{{ route('pos.index') }}" class="btn btn-outline-success">
                <i class="bi bi-cart3 me-1"></i> POS Baru
            </a>
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-info-circle me-1 text-primary"></i> Informasi Transaksi</h3>
                </div>
                <div class="card-body p-3">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Nomor Invoice</dt>
                        <dd class="col-7 fw-bold font-monospace text-dark">{{ $sale->invoice_number }}</dd>

                        <dt class="col-5 text-muted">Kasir Bertugas</dt>
                        <dd class="col-7">{{ $sale->user?->name ?? '-' }}</dd>

                        <dt class="col-5 text-muted">Waktu</dt>
                        <dd class="col-7">{{ $sale->created_at->format('d/m/Y H:i') }} WIB</dd>

                        <dt class="col-5 text-muted">Metode Bayar</dt>
                        <dd class="col-7">
                            <span class="badge bg-body-secondary text-dark border">
                                {{ $sale->payment_method->label() }}
                            </span>
                        </dd>

                        <dt class="col-5 text-muted">Total Jumlah</dt>
                        <dd class="col-7 tabular-nums fw-semibold">{{ $sale->items->sum('quantity') }} unit</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-bag-check me-1 text-success"></i> Rincian Barang Belanja</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col" class="text-center">Qty</th>
                                    <th scope="col" class="text-end">Harga Satuan</th>
                                    <th scope="col" class="text-end">Diskon Item</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale->items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->product_name }}</div>
                                            <small class="text-muted">
                                                <i class="bi bi-upc me-1"></i>{{ $item->sku }} &middot;
                                                <span class="badge bg-light text-muted border">{{ $item->product_type->label() }}</span>
                                            </small>
                                            @if ($item->digitalDetail)
                                                <div class="mt-1">
                                                    @if ($item->digitalDetail->status->value === 'completed')
                                                        <span class="badge badge-status-completed">
                                                            <i class="bi bi-check2-all"></i> Layanan Selesai
                                                        </span>
                                                    @elseif ($item->digitalDetail->status->value === 'pending')
                                                        <span class="badge badge-status-pending">
                                                            <i class="bi bi-hourglass-split"></i> Diproses
                                                        </span>
                                                    @else
                                                        <span class="badge badge-status-failed">
                                                            <i class="bi bi-exclamation-octagon-fill"></i> Gagal
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center tabular-nums fw-semibold">{{ $item->quantity }}</td>
                                        <td class="text-end tabular-nums text-muted small">{{ \App\Support\Money::format($item->unit_price) }}</td>
                                        <td class="text-end tabular-nums text-danger small">
                                            @if ((float) $item->discount_amount > 0)
                                                -{{ \App\Support\Money::format($item->discount_amount) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold tabular-nums">{{ \App\Support\Money::format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-calculator me-1 text-primary"></i> Ringkasan Pembayaran</h3>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">Subtotal Barang</span>
                        <span class="tabular-nums fw-semibold">{{ \App\Support\Money::format($sale->subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">
                            Potongan Diskon
                            @if ($sale->discount_type)
                                <span class="badge bg-light text-muted border">
                                    {{ $sale->discount_type->label() }}
                                </span>
                            @endif
                        </span>
                        <span class="text-danger tabular-nums fw-semibold">
                            @if ((float) $sale->discount_amount > 0)
                                -{{ \App\Support\Money::format($sale->discount_amount) }}
                            @else
                                Rp 0
                            @endif
                        </span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-success-subtle rounded border border-success-subtle mb-3">
                        <span class="fs-6 fw-bold text-success-emphasis">Total Tagihan</span>
                        <span class="fs-4 fw-bold text-success tabular-nums">{{ \App\Support\Money::format($sale->total) }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="text-muted">Dibayar ({{ $sale->payment_method->label() }})</span>
                        <span class="tabular-nums fw-semibold">{{ \App\Support\Money::format($sale->paid_amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Kembalian</span>
                        <span class="tabular-nums fw-bold {{ (float) $sale->change_amount > 0 ? 'text-success' : '' }}">
                            {{ \App\Support\Money::format($sale->change_amount) }}
                        </span>
                    </div>

                    @if ($sale->notes)
                        <hr class="my-2">
                        <div class="small text-muted">
                            <strong>Catatan:</strong> {{ $sale->notes }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if (auth()->user()->isOwner())
            <div class="col-12 col-lg-7">
                <div class="card h-100 shadow-sm border-primary-subtle">
                    <div class="card-header bg-primary-subtle border-primary-subtle py-2 px-3">
                        <h3 class="card-title fw-bold text-primary-emphasis mb-0">
                            <i class="bi bi-graph-up-arrow me-1"></i> Analisis Pendapatan & Laba Kotor (Owner)
                        </h3>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-muted">Pendapatan Diakui (Barang fisik + Layanan selesai)</span>
                            <strong class="tabular-nums">{{ \App\Support\Money::formatCents($recognizedRevenueCents) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 small">
                            <span class="text-muted">HPP Snapshot Modal Toko</span>
                            <span class="text-danger tabular-nums fw-semibold">-{{ \App\Support\Money::formatCents($recognizedCogsCents) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-light rounded border mb-3">
                            <span class="fw-bold text-dark">Laba Kotor Transaksi Ini</span>
                            <strong class="fs-5 text-success tabular-nums">
                                {{ \App\Support\Money::formatCents($recognizedRevenueCents - $recognizedCogsCents) }}
                            </strong>
                        </div>

                        @if ($pendingDigitalCents > 0)
                            <div class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-0">
                                <i class="bi bi-hourglass-split text-warning fs-5 flex-shrink-0"></i>
                                <div>
                                    Sebesar <strong>{{ \App\Support\Money::formatCents($pendingDigitalCents) }}</strong> merupakan transaksi layanan digital yang belum selesai. Pendapatan akan diakui saat status pemenuhan selesai.
                                </div>
                            </div>
                        @else
                            <p class="small text-muted mb-0">
                                <i class="bi bi-check-circle text-success me-1"></i> Seluruh item dalam transaksi ini sudah diakui dalam laporan laba kotor.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    @php($digitalItems = $sale->items->filter(fn ($item) => $item->digitalDetail !== null))
    @if ($digitalItems->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light py-2 px-3">
                <h3 class="card-title fw-bold mb-0">
                    <i class="bi bi-phone text-info me-1"></i> Data Layanan Digital Pelanggan
                </h3>
            </div>
            <div class="card-body p-3">
                @foreach ($digitalItems as $item)
                    @php($detail = $item->digitalDetail)
                    <div class="border rounded-3 p-3 mb-3 bg-body-tertiary">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $item->product_name }}</h5>
                                <small class="text-muted">
                                    {{ $detail->service_type === 'pulsa' ? 'Pulsa HP' : 'Token Listrik PLN' }}
                                    &middot; Provider: {{ $detail->provider ?: '-' }}
                                </small>
                            </div>
                            <div>
                                @if ($detail->status->value === 'completed')
                                    <span class="badge badge-status-completed"><i class="bi bi-check2-all"></i> Layanan Selesai</span>
                                @elseif ($detail->status->value === 'pending')
                                    <span class="badge badge-status-pending"><i class="bi bi-hourglass-split"></i> Belum Selesai</span>
                                @else
                                    <span class="badge badge-status-failed"><i class="bi bi-exclamation-octagon-fill"></i> Layanan Gagal</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-2 small mb-3">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Target Tujuan:</span>
                                <strong class="font-monospace text-dark">{{ $detail->phone_number ?? $detail->customer_id ?? $detail->meter_number ?? '-' }}</strong>
                            </div>
                            @if ($detail->meter_number && $detail->customer_id)
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">Nomor Meter:</span>
                                    <strong class="font-monospace text-dark">{{ $detail->meter_number }}</strong>
                                </div>
                            @endif
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Nominal Produk:</span>
                                <strong class="tabular-nums text-dark">{{ \App\Support\Money::format($detail->nominal) }}</strong>
                            </div>
                            @if ($detail->token_number)
                                <div class="col-12 col-md-3">
                                    <span class="text-muted d-block">Nomor Token:</span>
                                    <span class="font-monospace fw-bold text-success bg-white px-2 py-1 border rounded tabular-nums">{{ $detail->token_number }}</span>
                                </div>
                            @endif
                            @if ($detail->provider_reference)
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">Ref Provider:</span>
                                    <span class="font-monospace text-muted">{{ $detail->provider_reference }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($detail->isPending())
                            <div class="p-3 bg-white border rounded">
                                <h6 class="fw-bold small text-muted text-uppercase mb-2">Tandai Status Layanan Digital</h6>
                                <form action="{{ route('digital.update', $detail) }}" method="POST" class="row g-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="completed">Selesai (Terkirim)</option>
                                            <option value="failed">Gagal</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small fw-semibold">Ref Provider (Opsional)</label>
                                        <input type="text" name="provider_reference" class="form-control form-control-sm font-monospace" maxlength="100" placeholder="SN / Kode Ref">
                                    </div>
                                    @if ($detail->service_type === 'token')
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small fw-semibold">Nomor Token (20 Digit)</label>
                                            <input type="text" name="token_number" class="form-control form-control-sm font-monospace" maxlength="100" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX">
                                        </div>
                                    @endif
                                    <div class="col-12 col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                                            <i class="bi bi-save me-1"></i> Simpan Status
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small text-muted">Catatan Tambahan (Wajib jika status Gagal)</label>
                                        <input type="text" name="notes" class="form-control form-control-sm" maxlength="500" placeholder="Contoh: Gangguan provider PLN / nomor keliru">
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@stop

@push('js')
    @if (session('checkout_completed'))
        <script>
            try {
                sessionStorage.removeItem('pm_pos_state');
            } catch (error) {
            }
        </script>
    @endif
@endpush
