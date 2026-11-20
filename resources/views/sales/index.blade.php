@extends('adminlte::page')

@section('title', 'Riwayat Transaksi - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-receipt text-success me-1"></i> Riwayat Transaksi Kasir</h3>
            <small class="text-muted">Daftar seluruh penjualan toko, struk, dan status transaksi.</small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-cart3 me-1"></i> Buka Kasir (POS)
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="invoice" class="form-label small fw-semibold mb-1">Nomor Invoice</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-secondary"><i class="bi bi-receipt"></i></span>
                        <input type="search" name="invoice" id="invoice" value="{{ $filters['invoice'] ?? '' }}" class="form-control"
                            placeholder="PM-2026...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-md-2">
                    <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                @if ($cashiers->isNotEmpty())
                    <div class="col-6 col-md-2">
                        <label for="user_id" class="form-label small fw-semibold mb-1">Kasir</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">Semua Kasir</option>
                            @foreach ($cashiers as $cashier)
                                <option value="{{ $cashier->id }}" @selected((int) ($filters['user_id'] ?? 0) === $cashier->id)>
                                    {{ $cashier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-6 col-md-2">
                    <label for="payment_method" class="form-label small fw-semibold mb-1">Metode Bayar</label>
                    <select name="payment_method" id="payment_method" class="form-select">
                        <option value="">Semua Metode</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->value }}" @selected(($filters['payment_method'] ?? '') === $method->value)>
                                {{ $method->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="digital_status" class="form-label small fw-semibold mb-1">Status Digital</label>
                    <select name="digital_status" id="digital_status" class="form-select">
                        <option value="">Semua</option>
                        <option value="pending" @selected(($filters['digital_status'] ?? '') === 'pending')>Belum Selesai</option>
                        <option value="completed" @selected(($filters['digital_status'] ?? '') === 'completed')>Selesai</option>
                        <option value="failed" @selected(($filters['digital_status'] ?? '') === 'failed')>Gagal</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($sales->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-receipt display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada transaksi yang cocok dengan filter.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 140px;">Nomor Invoice</th>
                                <th scope="col">Waktu Transaksi</th>
                                <th scope="col">Kasir</th>
                                <th scope="col" class="text-center">Jumlah Item</th>
                                <th scope="col">Metode Pembayaran</th>
                                <th scope="col" class="text-center">Status Digital</th>
                                <th scope="col" class="text-end">Total Pembayaran</th>
                                <th scope="col" class="text-end" style="min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales.show', $sale) }}" class="fw-bold text-decoration-none">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">{{ $sale->created_at->format('d/m/Y H:i') }} WIB</td>
                                    <td>{{ $sale->user?->name ?? '-' }}</td>
                                    <td class="text-center tabular-nums">{{ $sale->items_count }} unit</td>
                                    <td>
                                        <span class="badge bg-body-secondary text-dark border">
                                            {{ $sale->payment_method->label() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($sale->pending_digital_count > 0)
                                            <span class="badge badge-status-pending">
                                                <i class="bi bi-hourglass-split"></i> {{ $sale->pending_digital_count }} diproses
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success tabular-nums">
                                        {{ \App\Support\Money::format($sale->total) }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="table-actions">
                                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary"
                                                title="Lihat rincian transaksi" aria-label="Lihat rincian">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            <a href="{{ route('sales.receipt', $sale) }}" class="btn btn-sm btn-outline-secondary" target="_blank"
                                                title="Cetak struk kasir" aria-label="Cetak struk">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }} dari total {{ $sales->total() }} transaksi
                    </div>
                    <div>
                        {{ $sales->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
