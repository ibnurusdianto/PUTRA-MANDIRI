<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice_number }} - {{ config('store.name') }}</title>
    <style>
        :root {
            --receipt-width: {{ config('store.receipt_width_mm', 58) }}mm;
            --receipt-font-size: 11px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 16px 8px;
            background: #f1f5f9;
            font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace;
            font-size: var(--receipt-font-size);
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .receipt-card {
            width: var(--receipt-width);
            max-width: 100%;
            margin: 0 auto;
            padding: 4mm 3mm;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        .center { text-align: center; }
        .store-name {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .divider-double {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 2px 0;
            vertical-align: top;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .items td { padding-top: 3px; }
        .total-row td {
            font-weight: bold;
            font-size: 12px;
            padding-top: 5px;
        }
        .small { font-size: 10px; }
        .token-box {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            word-break: break-all;
            padding: 2px 4px;
            border: 1px solid #000;
            display: inline-block;
            margin-top: 2px;
        }
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
        .preview-toolbar {
            max-width: 480px;
            margin: 0 auto 16px auto;
            padding: 10px 14px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            font-family: system-ui, sans-serif;
        }
        .toolbar-group {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            line-height: 1;
        }
        .btn-primary { background: #15803d; color: #fff; border-color: #15803d; }
        .btn-primary:hover { background: #166534; }
        .btn-secondary { background: #f8fafc; color: #334155; border-color: #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-width-toggle {
            padding: 4px 8px;
            font-size: 11px;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-width-toggle.active {
            background: #15803d;
            color: #fff;
            border-color: #15803d;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .preview-toolbar {
                display: none !important;
            }
            .receipt-card {
                width: var(--receipt-width) !important;
                margin: 0 !important;
                padding: 2mm !important;
                box-shadow: none !important;
                border: none !important;
            }
            @page {
                size: var(--receipt-width) auto;
                margin: 2mm;
            }
        }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <div class="toolbar-group">
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                Cetak Struk
            </button>
            <a href="{{ route('pos.index') }}" class="btn btn-secondary">POS Baru</a>
            <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">Detail</a>
        </div>
        <div class="toolbar-group">
            <span style="font-size: 11px; color: #64748b;">Lebar:</span>
            <button type="button" class="btn-width-toggle active" id="btn-58" onclick="setWidth(58)">58mm</button>
            <button type="button" class="btn-width-toggle" id="btn-80" onclick="setWidth(80)">80mm</button>
        </div>
    </div>

    <div class="receipt-card" id="receipt-card">
        <div class="center">
            <div class="store-name">{{ config('store.name') }}</div>
            @if (config('store.address'))
                <div class="small">{{ config('store.address') }}</div>
            @endif
            @if (config('store.phone'))
                <div class="small">Telp: {{ config('store.phone') }}</div>
            @endif
            <div class="small">TOKO KEBUTUHAN SEHARI-HARI 24 JAM</div>
        </div>

        <div class="divider"></div>

        <table class="small">
            <tr>
                <td>Invoice</td>
                <td class="text-end fw-bold">{{ $sale->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-end">{{ $sale->created_at->format('d/m/Y H:i') }} WIB</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td class="text-end">{{ $sale->user?->name ?? 'Kasir Toko' }}</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td class="text-end">{{ $sale->payment_method->label() }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="items">
            @foreach ($sale->items as $item)
                <tr>
                    <td colspan="2" style="font-weight: 600;">{{ $item->product_name }}</td>
                </tr>
                <tr class="small">
                    <td>{{ $item->quantity }} x {{ \App\Support\Money::format($item->unit_price) }}</td>
                    <td class="text-end tabular-nums">{{ \App\Support\Money::format($item->subtotal) }}</td>
                </tr>
                @if ((float) $item->discount_amount > 0)
                    <tr class="small" style="color: #475569;">
                        <td>&nbsp; Potongan item</td>
                        <td class="text-end tabular-nums">-{{ \App\Support\Money::format($item->discount_amount) }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        <div class="divider"></div>

        <table>
            <tr class="small">
                <td>Subtotal</td>
                <td class="text-end tabular-nums">{{ \App\Support\Money::format($sale->subtotal) }}</td>
            </tr>
            @if ((float) $sale->discount_amount > 0)
                <tr class="small">
                    <td>Diskon @if ($sale->discount_type) ({{ $sale->discount_type->label() }}) @endif</td>
                    <td class="text-end tabular-nums">-{{ \App\Support\Money::format($sale->discount_amount) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="text-end tabular-nums">{{ \App\Support\Money::format($sale->total) }}</td>
            </tr>
            <tr class="small">
                <td>Bayar ({{ $sale->payment_method->label() }})</td>
                <td class="text-end tabular-nums">{{ \App\Support\Money::format($sale->paid_amount) }}</td>
            </tr>
            @if ($sale->payment_method->isCash())
                <tr class="small">
                    <td>Kembalian</td>
                    <td class="text-end tabular-nums">{{ \App\Support\Money::format($sale->change_amount) }}</td>
                </tr>
            @endif
        </table>

        @php($digitalItems = $sale->items->filter(fn ($item) => $item->digitalDetail !== null))
        @if ($digitalItems->isNotEmpty())
            <div class="divider"></div>
            <div class="small">
                <div style="font-weight: bold; margin-bottom: 2px;">LAYANAN DIGITAL</div>
                @foreach ($digitalItems as $item)
                    @php($detail = $item->digitalDetail)
                    <div style="margin-top: 4px; padding-left: 2px;">
                        <strong>{{ $item->product_name }}</strong><br>
                        Tujuan: {{ $detail->phone_number ?? $detail->customer_id ?? $detail->meter_number ?? '-' }}<br>
                        Status: {{ $detail->status->label() }}
                        @if ($detail->status->value === 'completed' && $detail->token_number)
                            <br>No. Token:<br>
                            <span class="token-box">{{ $detail->token_number }}</span>
                        @endif
                        @if ($detail->provider_reference)
                            <br>Ref: {{ $detail->provider_reference }}
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($sale->notes)
            <div class="divider"></div>
            <div class="small">Catatan: {{ $sale->notes }}</div>
        @endif

        <div class="divider-double"></div>

        <div class="center small">
            {{ config('store.receipt_footer') }}<br>
            Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.<br>
            Layanan Pelanggan 24 Jam
        </div>
    </div>

    <script>
        function setWidth(width) {
            document.documentElement.style.setProperty('--receipt-width', width + 'mm');
            document.getElementById('btn-58').classList.toggle('active', width === 58);
            document.getElementById('btn-80').classList.toggle('active', width === 80);
        }
    </script>
</body>
</html>
