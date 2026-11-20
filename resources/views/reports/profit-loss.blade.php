@extends('adminlte::page')

@section('title', 'Laba Rugi Sederhana - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-calculator text-success me-1"></i> Laba Rugi Sederhana Toko</h3>
            <small class="text-muted">Perhitungan laba kotor, beban operasional, dan estimasi laba bersih toko 24 jam.</small>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('reports.sales', request()->query()) }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-graph-up me-1"></i> Laporan Penjualan
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash')

    @include('reports.partials.filters', ['action' => route('reports.profit-loss')])

    @php($summary = $report['summary'])

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-receipt-cutoff text-success me-1"></i> Ringkasan Laba Rugi Periode</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="ps-3">Jumlah Transaksi Kasir</td>
                                    <td class="text-end pe-3 tabular-nums fw-semibold">{{ $summary['transaction_count'] }} transaksi</td>
                                </tr>
                                <tr>
                                    <td class="ps-3">Penjualan Kotor (Katalog sebelum diskon)</td>
                                    <td class="text-end pe-3 tabular-nums">{{ \App\Support\Money::formatCents($summary['gross_sales_cents']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 text-muted">Total Potongan Diskon Pelanggan</td>
                                    <td class="text-end pe-3 text-danger tabular-nums fw-semibold">-{{ \App\Support\Money::formatCents($summary['discount_cents']) }}</td>
                                </tr>
                                <tr class="bg-body-secondary fw-semibold">
                                    <td class="ps-3">Penerimaan Kasir (Total Uang Masuk)</td>
                                    <td class="text-end pe-3 tabular-nums text-dark">{{ \App\Support\Money::formatCents($summary['net_sales_cents']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">Pendapatan Diakui</div>
                                        <small class="text-muted">Barang fisik dan layanan digital yang statusnya sudah selesai</small>
                                    </td>
                                    <td class="text-end pe-3 tabular-nums fw-semibold">{{ \App\Support\Money::formatCents($summary['recognized_revenue_cents']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 text-muted">HPP / Harga Pokok Penjualan (Snapshot Modal Toko)</td>
                                    <td class="text-end pe-3 text-danger tabular-nums fw-semibold">-{{ \App\Support\Money::formatCents($summary['cogs_cents']) }}</td>
                                </tr>
                                <tr class="bg-success-subtle border-top border-bottom border-success-subtle">
                                    <td class="ps-3 fw-bold text-success-emphasis fs-6">Laba Kotor Penjualan</td>
                                    <td class="text-end pe-3 fs-5 fw-bold text-success tabular-nums">{{ \App\Support\Money::formatCents($summary['gross_profit_cents']) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 text-muted">Biaya Pengeluaran Operasional Toko</td>
                                    <td class="text-end pe-3 text-danger tabular-nums fw-semibold">-{{ \App\Support\Money::formatCents($summary['expenses_cents']) }}</td>
                                </tr>
                                <tr class="bg-dark text-white border-top">
                                    <td class="ps-3 py-3 fs-5 fw-bold">Estimasi Laba Bersih Toko</td>
                                    <td class="text-end pe-3 py-3 fs-4 fw-bold tabular-nums {{ $summary['net_profit_cents'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ \App\Support\Money::formatCents($summary['net_profit_cents']) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-info-circle text-primary me-1"></i> Status Pengakuan</h3>
                </div>
                <div class="card-body p-3">
                    <div class="p-3 bg-warning-subtle rounded border border-warning-subtle mb-3">
                        <span class="text-warning-emphasis small fw-bold text-uppercase d-block mb-1">Layanan Digital Belum Selesai</span>
                        <div class="fs-3 fw-bold text-dark my-1 tabular-nums">{{ \App\Support\Money::formatCents($summary['pending_digital_cents']) }}</div>
                        <div class="small text-muted">{{ $summary['pending_digital_count'] }} transaksi pulsa/token masih diproses</div>
                    </div>
                    <div class="small text-muted">
                        <p class="mb-2">
                            <strong>Prinsip Akuntansi Sederhana:</strong>
                        </p>
                        <ul class="ps-3 mb-0">
                            <li class="mb-1">Pembayaran pulsa / token yang belum selesai tidak dihitung sebagai laba hingga terkirim.</li>
                            <li class="mb-1">HPP dihitung berbasis harga modal saat transaksi dibuat.</li>
                            <li class="mb-0">Pengeluaran operasional mencakup listrik, air, dan beban rutin toko.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light py-2 px-3">
            <h3 class="card-title fw-bold mb-0"><i class="bi bi-wallet2 text-danger me-1"></i> Rincian Beban Operasional per Kategori</h3>
        </div>
        <div class="card-body p-0">
            @if ($report['expense_categories']->isEmpty())
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">Tidak ada pengeluaran operasional pada periode ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 200px;">Kategori Pengeluaran</th>
                                <th scope="col" class="text-end">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['expense_categories'] as $row)
                                <tr>
                                    <td class="fw-semibold">
                                        <span class="badge bg-body-secondary text-dark border me-2">{{ $row->category }}</span>
                                    </td>
                                    <td class="text-end fw-bold tabular-nums text-danger">{{ \App\Support\Money::format($row->total) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-light fw-bold">
                                <td>TOTAL BEBAN OPERASIONAL</td>
                                <td class="text-end text-danger fs-6 tabular-nums">{{ \App\Support\Money::formatCents($summary['expenses_cents']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
