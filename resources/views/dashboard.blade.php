@extends('adminlte::page')

@section('title', 'Dashboard ' . ($isOwner ? 'Owner' : 'Kasir') . ' - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold">Dashboard {{ $isOwner ? 'Owner' : 'Kasir' }}</h3>
            <small class="text-muted">{{ config('store.name') }} &middot; Layanan Toko 24 Jam &middot; {{ now()->translatedFormat('l, d F Y') }}</small>
        </div>
        <a href="{{ route('pos.index') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-cart3 me-1"></i> Buka Kasir (POS)
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')
    <div class="row g-3 mb-2">
        <div class="col-12 col-sm-6 col-lg-3">
            <x-adminlte-small-box
                :title="\App\Support\Money::formatCents($stats['sales_total_cents'])"
                :text="$isOwner ? 'Penjualan hari ini (penerimaan)' : 'Total penjualan saya hari ini'"
                icon="bi bi-cash-coin" theme="success" />
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <x-adminlte-small-box
                :title="$stats['transaction_count']"
                :text="$isOwner ? 'Transaksi hari ini' : 'Transaksi saya hari ini'"
                icon="bi bi-receipt" theme="primary" />
        </div>
        @if ($isOwner)
            <div class="col-12 col-sm-6 col-lg-3">
                <x-adminlte-small-box
                    :title="\App\Support\Money::formatCents($grossProfitCents)"
                    text="Laba kotor hari ini (diakui)"
                    icon="bi bi-graph-up-arrow" theme="info" />
            </div>
        @else
            <div class="col-12 col-sm-6 col-lg-3">
                <x-adminlte-small-box
                    :title="\App\Support\Money::formatCents(intdiv($stats['sales_total_cents'], max(1, $stats['transaction_count'])))"
                    text="Rata-rata per transaksi saya"
                    icon="bi bi-calculator" theme="info" />
            </div>
        @endif
        <div class="col-12 col-sm-6 col-lg-3">
            <x-adminlte-small-box
                :title="$stats['active_products']"
                text="Produk aktif di katalog"
                icon="bi bi-box-seam" theme="warning" />
        </div>
    </div>
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-lg-3">
            @if ($isOwner)
                <a href="{{ route('products.index', ['status' => 'low']) }}" class="text-decoration-none">
            @endif
                <div class="info-box h-100 mb-0 shadow-sm">
                    <span class="info-box-icon text-bg-warning"><i class="bi bi-exclamation-triangle-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Stok menipis</span>
                        <span class="info-box-number fs-5 text-dark">{{ $stats['low_stock_count'] }} produk</span>
                    </div>
                </div>
            @if ($isOwner)
                </a>
            @endif
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            @if ($isOwner)
                <a href="{{ route('products.index', ['status' => 'out']) }}" class="text-decoration-none">
            @endif
                <div class="info-box h-100 mb-0 shadow-sm">
                    <span class="info-box-icon text-bg-danger"><i class="bi bi-x-octagon-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-muted">Stok habis</span>
                        <span class="info-box-number fs-5 text-dark">{{ $stats['out_of_stock_count'] }} produk</span>
                    </div>
                </div>
            @if ($isOwner)
                </a>
            @endif
        </div>
        @if ($isOwner)
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('digital.index', ['status' => 'pending']) }}" class="text-decoration-none">
                    <div class="info-box h-100 mb-0 shadow-sm">
                        <span class="info-box-icon text-bg-info"><i class="bi bi-hourglass-split"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Layanan digital proses</span>
                            <span class="info-box-number fs-5 text-dark">{{ $pendingDigitalCount }} layanan</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <a href="{{ route('reports.sales') }}" class="text-decoration-none">
                    <div class="info-box h-100 mb-0 shadow-sm">
                        <span class="info-box-icon text-bg-primary"><i class="bi bi-bar-chart-fill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Laporan Penjualan</span>
                            <span class="info-box-number fs-6 text-primary">Buka Laporan &rarr;</span>
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <h3 class="card-title fw-bold mb-0">
                        <i class="bi bi-bar-chart-line text-success me-1"></i> Penjualan 7 Hari Terakhir
                    </h3>
                    <span class="badge bg-light text-muted border">Minggu Berjalan</span>
                </div>
                <div class="card-body p-3">
                    @php($totalChartCents = collect($chart)->sum('total_cents'))
                    @if ($totalChartCents <= 0)
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart display-5 text-secondary mb-2"></i>
                            <p class="mb-0 fw-semibold">Belum ada transaksi dalam 7 hari terakhir.</p>
                            <small>Transaksi yang berhasil tercatat akan tampil pada grafik ini.</small>
                        </div>
                    @else
                        <div class="d-flex align-items-end justify-content-between gap-1 gap-md-2" style="height: 190px;">
                            @foreach ($chart as $point)
                                @php($height = $chartMaxCents > 0 ? max(8, (int) round($point['total_cents'] / $chartMaxCents * 140)) : 8)
                                <div class="flex-fill text-center d-flex flex-column justify-content-end align-items-center h-100">
                                    <div class="small text-muted mb-1 text-truncate d-none d-md-block tabular-nums" style="font-size: 0.72rem;">
                                        {{ \App\Support\Money::formatCents($point['total_cents']) }}
                                    </div>
                                    <div class="bg-success rounded-top mx-auto"
                                         style="height: {{ $height }}px; width: 65%; min-width: 14px; max-width: 38px;"
                                         title="{{ $point['label'] }}: {{ \App\Support\Money::formatCents($point['total_cents']) }}"></div>
                                    <div class="small fw-semibold mt-1 text-truncate" style="font-size: 0.75rem;">{{ $point['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
                            <span>Akumulasi 7 hari: <strong class="text-dark tabular-nums">{{ \App\Support\Money::formatCents($totalChartCents) }}</strong></span>
                            <span class="d-md-none"><i class="bi bi-info-circle me-1"></i>Sentuh batang untuk nilai</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <h3 class="card-title fw-bold mb-0">
                        <i class="bi bi-exclamation-triangle text-warning me-1"></i> Stok Perlu Perhatian
                    </h3>
                    @if ($isOwner)
                        <a href="{{ route('products.index', ['status' => 'low']) }}" class="btn btn-sm btn-outline-secondary">Kelola</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($lowStockProducts->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check display-5 text-success mb-2"></i>
                            <p class="mb-0 fw-semibold text-dark">Seluruh stok dalam kondisi aman</p>
                            <small class="text-muted">Tidak ada produk aktif yang mencapai batas minimum.</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Barang</th>
                                        <th scope="col" class="text-end">Sisa Stok</th>
                                        <th scope="col" class="text-end">Min</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockProducts as $product)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold small text-truncate" style="max-width: 190px;">{{ $product->name }}</div>
                                                <small class="text-muted">{{ $product->sku }}</small>
                                            </td>
                                            <td class="text-end">
                                                @if ($product->stock <= 0)
                                                    <span class="badge badge-status-out">
                                                        <i class="bi bi-x-octagon-fill"></i> Habis
                                                    </span>
                                                @else
                                                    <span class="badge badge-status-low">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $product->stock }} {{ $product->unit }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end text-muted small tabular-nums">{{ $product->min_stock }}</td>
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
        <div class="col-lg-{{ $isOwner ? 6 : 12 }}">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <h3 class="card-title fw-bold mb-0">
                        <i class="bi bi-clock-history text-primary me-1"></i> Transaksi Terbaru Hari Ini
                    </h3>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentSales->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-receipt display-6 text-secondary mb-2"></i>
                            <p class="mb-0 fw-semibold text-dark">Belum ada transaksi hari ini</p>
                            <small class="text-muted">Gunakan Kasir (POS) untuk melayani pembeli.</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Invoice</th>
                                        <th scope="col">Kasir</th>
                                        <th scope="col">Waktu</th>
                                        <th scope="col" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $sale)
                                        <tr>
                                            <td>
                                                <a href="{{ route('sales.show', $sale) }}" class="fw-semibold text-decoration-none">
                                                    {{ $sale->invoice_number }}
                                                </a>
                                            </td>
                                            <td class="small">{{ $sale->user?->name ?? '-' }}</td>
                                            <td class="small text-muted">{{ $sale->created_at->format('H:i') }} WIB</td>
                                            <td class="text-end fw-semibold tabular-nums">{{ \App\Support\Money::format($sale->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($isOwner)
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                        <h3 class="card-title fw-bold mb-0">
                            <i class="bi bi-trophy text-warning me-1"></i> Produk Terlaris 7 Hari
                        </h3>
                        <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-secondary">Laporan</a>
                    </div>
                    <div class="card-body p-0">
                        @if ($bestSellers->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam display-6 text-secondary mb-2"></i>
                                <p class="mb-0 fw-semibold text-dark">Belum ada data penjualan 7 hari</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Nama Barang</th>
                                            <th scope="col" class="text-end">Terjual</th>
                                            <th scope="col" class="text-end">Omzet</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bestSellers as $row)
                                            <tr>
                                                <td class="fw-semibold small text-truncate" style="max-width: 200px;">
                                                    {{ $row->product_name }}
                                                </td>
                                                <td class="text-end tabular-nums">{{ (int) $row->total_quantity }} unit</td>
                                                <td class="text-end fw-semibold tabular-nums">{{ \App\Support\Money::format($row->total_revenue) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
    @if ($isOwner && $pendingDigitalServices->isNotEmpty())
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-warning-subtle border-warning-subtle">
                <h3 class="card-title fw-bold text-warning-emphasis mb-0">
                    <i class="bi bi-hourglass-split me-1"></i> Layanan Digital Menunggu Penyelesaian
                </h3>
                <a href="{{ route('digital.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning">
                    Kelola Antrean ({{ $pendingDigitalServices->count() }})
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Invoice</th>
                                <th scope="col">Layanan</th>
                                <th scope="col">Nomor Tujuan / Meter</th>
                                <th scope="col">Kasir</th>
                                <th scope="col">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingDigitalServices as $service)
                                <tr>
                                    <td>
                                        <a href="{{ route('sales.show', $service->saleItem->sale) }}" class="fw-semibold">
                                            {{ $service->saleItem?->sale?->invoice_number ?? '-' }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small">{{ $service->saleItem?->product_name ?? '-' }}</div>
                                        <small class="text-muted">{{ $service->service_type === 'pulsa' ? 'Pulsa' : 'Token Listrik' }}</small>
                                    </td>
                                    <td class="tabular-nums fw-semibold">
                                        {{ $service->phone_number ?? $service->customer_id ?? $service->meter_number ?? '-' }}
                                    </td>
                                    <td class="small">{{ $service->saleItem?->sale?->user?->name ?? '-' }}</td>
                                    <td class="small text-muted">{{ $service->created_at->format('d/m H:i') }} WIB</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@stop
