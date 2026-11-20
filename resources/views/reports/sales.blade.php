@extends('adminlte::page')

@section('title', 'Laporan Penjualan - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-graph-up text-success me-1"></i> Laporan Penjualan Toko</h3>
            <small class="text-muted">Ringkasan transaksi kasir, metode pembayaran, omzet kasir, dan pengakuan pendapatan.</small>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('reports.profit-loss', request()->query()) }}" class="btn btn-outline-primary fw-semibold">
                <i class="bi bi-calculator me-1"></i> Laba Rugi Sederhana
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.flash')

    @include('reports.partials.filters', ['action' => route('reports.sales')])

    @php($summary = $report['summary'])
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 bg-primary text-white h-100">
                <div class="card-body p-3">
                    <span class="text-white-50 small fw-bold text-uppercase">Jumlah Transaksi</span>
                    <div class="display-6 fw-bold my-1 tabular-nums">{{ $summary['transaction_count'] }}</div>
                    <div class="text-white-50 small">Struk tercatat</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 bg-success text-white h-100">
                <div class="card-body p-3">
                    <span class="text-white-50 small fw-bold text-uppercase">Penjualan Kotor (Katalog)</span>
                    <div class="fs-3 fw-bold my-1 tabular-nums">{{ \App\Support\Money::formatCents($summary['gross_sales_cents']) }}</div>
                    <div class="text-white-50 small">Sebelum potongan diskon</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 bg-danger text-white h-100">
                <div class="card-body p-3">
                    <span class="text-white-50 small fw-bold text-uppercase">Total Potongan Diskon</span>
                    <div class="fs-3 fw-bold my-1 tabular-nums">-{{ \App\Support\Money::formatCents($summary['discount_cents']) }}</div>
                    <div class="text-white-50 small">Diskon transaksi & item</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 bg-dark text-white h-100">
                <div class="card-body p-3">
                    <span class="text-white-50 small fw-bold text-uppercase">Penerimaan Kasir Bersih</span>
                    <div class="fs-3 fw-bold my-1 tabular-nums text-success">{{ \App\Support\Money::formatCents($summary['net_sales_cents']) }}</div>
                    <div class="text-white-50 small">Uang masuk ke kasir</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-wallet-fill text-success me-1"></i> Pengakuan Pendapatan & HPP</h3>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">Pendapatan Diakui (Barang fisik + Layanan digital selesai)</span>
                        <strong class="tabular-nums">{{ \App\Support\Money::formatCents($summary['recognized_revenue_cents']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">HPP Modal Toko (Snapshot detail transaksi)</span>
                        <span class="text-danger tabular-nums fw-semibold">-{{ \App\Support\Money::formatCents($summary['cogs_cents']) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-success-subtle rounded border border-success-subtle mb-3">
                        <span class="fw-bold text-success-emphasis">Laba Kotor Penjualan</span>
                        <strong class="fs-4 text-success tabular-nums">{{ \App\Support\Money::formatCents($summary['gross_profit_cents']) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Layanan digital belum selesai ({{ $summary['pending_digital_count'] }} transaksi)</span>
                        <strong class="text-warning tabular-nums">{{ \App\Support\Money::formatCents($summary['pending_digital_cents']) }}</strong>
                    </div>
                    <p class="small text-muted mb-0 mt-2">
                        <i class="bi bi-info-circle me-1"></i> Pembayaran layanan digital baru diakui sebagai omzet setelah pulsa atau token terkirim sukses ke pembeli.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-credit-card-2-front text-primary me-1"></i> Rekap Metode Pembayaran</h3>
                </div>
                <div class="card-body p-0">
                    @if ($report['payment_methods']->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <p class="mb-0">Belum ada transaksi pada periode ini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Metode Pembayaran</th>
                                        <th scope="col" class="text-center">Jumlah Transaksi</th>
                                        <th scope="col" class="text-end">Total Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['payment_methods'] as $row)
                                        <tr>
                                            <td class="fw-bold">{{ $row->payment_method->label() }}</td>
                                            <td class="text-center tabular-nums">{{ $row->transaction_count }}</td>
                                            <td class="text-end fw-bold tabular-nums text-success">{{ \App\Support\Money::format($row->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-person-badge text-primary me-1"></i> Penjualan per Kasir</h3>
                </div>
                <div class="card-body p-0">
                    @if ($report['cashiers']->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <p class="mb-0">Belum ada transaksi pada periode ini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Kasir Bertugas</th>
                                        <th scope="col" class="text-center">Transaksi</th>
                                        <th scope="col" class="text-end">Total Omzet</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['cashiers'] as $row)
                                        <tr>
                                            <td class="fw-semibold">{{ $row->cashier_name }}</td>
                                            <td class="text-center tabular-nums">{{ $row->transaction_count }}</td>
                                            <td class="text-end fw-bold tabular-nums">{{ \App\Support\Money::format($row->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-light py-2 px-3">
                    <h3 class="card-title fw-bold mb-0"><i class="bi bi-trophy text-warning me-1"></i> Barang Terlaris (Diakui)</h3>
                </div>
                <div class="card-body p-0">
                    @if ($report['top_products']->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <p class="mb-0">Belum ada penjualan yang diakui pada periode ini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Nama Barang</th>
                                        <th scope="col">SKU</th>
                                        <th scope="col" class="text-end">Terjual</th>
                                        <th scope="col" class="text-end">Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['top_products'] as $row)
                                        <tr>
                                            <td class="fw-bold">{{ $row->product_name }}</td>
                                            <td class="small text-muted">{{ $row->sku }}</td>
                                            <td class="text-end tabular-nums">{{ (int) $row->total_quantity }} unit</td>
                                            <td class="text-end fw-bold tabular-nums text-success">{{ \App\Support\Money::format($row->total_revenue) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light py-2 px-3">
            <h3 class="card-title fw-bold mb-0"><i class="bi bi-calendar-check me-1 text-success"></i> Rincian Penjualan Harian</h3>
        </div>
        <div class="card-body p-0">
            @php($maxDaily = max(1, collect($report['daily'])->max('total_cents')))
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-center">Jumlah Transaksi</th>
                            <th scope="col" class="text-end">Total Penerimaan</th>
                            <th scope="col" style="width: 35%;" class="d-none d-md-table-cell">Proporsi Relatif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['daily'] as $row)
                            <tr>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('l, d/m/Y') }}</td>
                                <td class="text-center tabular-nums">{{ $row['transaction_count'] }}</td>
                                <td class="text-end fw-bold tabular-nums text-success">{{ \App\Support\Money::formatCents($row['total_cents']) }}</td>
                                <td class="d-none d-md-table-cell">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $row['total_cents'] > 0 ? max(2, round($row['total_cents'] / $maxDaily * 100)) : 0 }}%"
                                            aria-valuenow="{{ $row['total_cents'] }}" aria-valuemin="0" aria-valuemax="{{ $maxDaily }}"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-secondary py-2 px-3 small text-muted mb-0">
        <i class="bi bi-shield-lock me-1"></i>
        Laporan ini bersifat operasional toko minimarket 24 jam. HPP dihitung dari harga modal snapshot saat transaksi selesai, bukan dari harga katalog produk terbaru.
    </div>
@stop
