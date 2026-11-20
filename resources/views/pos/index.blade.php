@extends('adminlte::page')

@section('title', 'Kasir - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-cart3 text-success me-1"></i> Kasir (POS)</h3>
            <small class="text-muted">Pindai barcode atau cari nama barang/SKU untuk memulai transaksi.</small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-none d-md-flex align-items-center gap-2 text-muted small bg-body-secondary px-3 py-1 rounded-pill">
                <span><kbd>F2</kbd> Cari Produk</span>
                <span>&bull;</span>
                <span><kbd>F8</kbd> Pembayaran</span>
            </div>
            <div class="text-muted small">
                <i class="bi bi-clock me-1"></i> {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="pos-page-wrapper">
        @include('partials.flash')

        <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3 shadow-sm">
            <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
            <div>{{ config('store.digital_manual_notice') }}</div>
        </div>

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="card mb-3 shadow-sm">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-7">
                                <label for="product-search" class="form-label fw-semibold small mb-1">
                                    <i class="bi bi-search me-1"></i> Cari Barang / Scan Barcode
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-secondary"><i class="bi bi-upc-scan fs-5 text-success"></i></span>
                                    <input type="search" id="product-search" class="form-control form-control-lg"
                                        placeholder="Nama / SKU / barcode (Tekan Enter)" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="category-filter" class="form-label fw-semibold small mb-1">
                                    <i class="bi bi-tags me-1"></i> Kategori
                                </label>
                                <select id="category-filter" class="form-select form-select-lg">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="product-grid" class="row g-2"></div>
                <div id="product-empty" class="card text-center text-muted py-5 d-none shadow-sm">
                    <div class="card-body">
                        <i class="bi bi-search display-5 text-secondary mb-2"></i>
                        <p class="mt-2 mb-0 fw-semibold text-dark">Barang tidak ditemukan</p>
                        <small class="text-muted">Coba kata kunci pencarian lain atau pilih kategori Semua.</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-5 offcanvas-xl offcanvas-end" tabindex="-1" id="posCartOffcanvas" aria-labelledby="posCartOffcanvasLabel">
                <div class="offcanvas-header d-xl-none border-bottom bg-success text-white py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-cart3 fs-4"></i>
                        <h5 class="offcanvas-title fw-bold mb-0" id="posCartOffcanvasLabel">Keranjang & Pembayaran</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#posCartOffcanvas" aria-label="Tutup"></button>
                </div>

                <div class="offcanvas-body p-0 p-xl-0">
                    <form action="{{ route('pos.checkout') }}" method="POST" id="checkout-form" class="pos-cart-panel w-100">
                        @csrf
                        <input type="hidden" name="checkout_token" id="checkout_token" value="{{ $checkoutToken }}">
                        <input type="hidden" name="items" id="items_payload" value="[]">
                        <div class="card mb-3 shadow-sm border">
                            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3 bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-cart-check fs-5 text-success"></i>
                                    <h3 class="card-title fw-bold mb-0">Keranjang Belanja</h3>
                                    <span class="badge bg-success rounded-pill px-2 py-1" id="cart-count-badge">0 item</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="clear-cart" title="Kosongkan seluruh keranjang (F4)">
                                    <i class="bi bi-trash me-1"></i> Kosongkan
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive pos-cart-items">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" style="min-width: 140px;">Item</th>
                                                <th scope="col" style="width: 105px;" class="text-center">Qty</th>
                                                <th scope="col" class="text-end" style="min-width: 90px;">Subtotal</th>
                                                <th scope="col" style="width: 44px;" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart-body"></tbody>
                                    </table>
                                </div>
                                <div id="cart-empty" class="text-center text-muted py-5 px-3">
                                    <i class="bi bi-basket3 display-5 text-secondary"></i>
                                    <p class="mt-2 mb-1 fw-semibold text-dark">Keranjang masih kosong</p>
                                    <small class="text-muted">Klik produk dari katalog atau scan barcode barang untuk menambahkan.</small>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-sm border">
                            <div class="card-header py-2 px-3 bg-light">
                                <h3 class="card-title fw-bold mb-0"><i class="bi bi-wallet2 text-primary me-1"></i> Ringkasan Pembayaran</h3>
                            </div>
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Subtotal Barang</span>
                                    <strong class="fs-6 tabular-nums" id="subtotal-display">Rp 0</strong>
                                </div>

                                <div class="row g-2 align-items-end mb-2">
                                    <div class="col-6">
                                        <label for="discount-type" class="form-label small fw-semibold mb-1">Diskon Transaksi</label>
                                        <select name="discount_type" id="discount-type" class="form-select form-select-sm">
                                            <option value="">Tanpa diskon</option>
                                            <option value="nominal">Nominal (Rp)</option>
                                            <option value="percent">Persentase (%)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label for="discount-value" class="form-label small fw-semibold mb-1">Nilai Diskon</label>
                                        <input type="number" name="discount_value" id="discount-value" class="form-control form-control-sm tabular-nums"
                                            min="0" step="0.01" value="0" disabled inputmode="decimal">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center text-muted small mb-2">
                                    <span>Potongan Diskon</span>
                                    <strong class="text-danger tabular-nums" id="discount-display">Rp 0</strong>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center py-2 px-3 bg-success-subtle rounded-3 mb-3 border border-success-subtle">
                                    <span class="fs-6 fw-bold text-success-emphasis">Total Tagihan</span>
                                    <span class="fs-3 fw-bold text-success tabular-nums" id="total-display">Rp 0</span>
                                </div>
                                <div class="mb-3">
                                    <label for="payment-method" class="form-label small fw-semibold mb-1">Metode Pembayaran</label>
                                    <select name="payment_method" id="payment-method" class="form-select">
                                        <option value="cash">Cash / Tunai</option>
                                        <option value="transfer">Transfer Bank (Konfirmasi Manual)</option>
                                        <option value="qris">QRIS (Konfirmasi Manual)</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="paid-wrapper">
                                    <label for="paid-amount" class="form-label small fw-semibold mb-1">
                                        Uang Tunai Diterima (Rp) <span class="text-danger" aria-hidden="true">*</span>
                                    </label>
                                    <input type="number" name="paid_amount" id="paid-amount" class="form-control form-control-lg tabular-nums fw-bold text-dark"
                                        min="0" step="1" value="0" inputmode="numeric">
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <button type="button" class="quick-cash-chip" data-cash="exact">Uang Pas</button>
                                        <button type="button" class="quick-cash-chip" data-cash="10000">10.000</button>
                                        <button type="button" class="quick-cash-chip" data-cash="20000">20.000</button>
                                        <button type="button" class="quick-cash-chip" data-cash="50000">50.000</button>
                                        <button type="button" class="quick-cash-chip" data-cash="100000">100.000</button>
                                    </div>
                                </div>
                                <div id="shortfall-alert" class="alert alert-warning py-2 px-3 small d-none mb-2" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                    <span>Uang tunai kurang <strong id="shortfall-amount" class="tabular-nums">Rp 0</strong></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-body-secondary rounded">
                                    <span class="text-muted fw-semibold small">Kembalian</span>
                                    <strong class="fs-5 tabular-nums" id="change-display">Rp 0</strong>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label small text-muted mb-1">Catatan Struk (opsional)</label>
                                    <textarea name="notes" id="notes" rows="1" maxlength="500" class="form-control form-control-sm" placeholder="Contoh: Titipan tetangga / diskon langganan"></textarea>
                                </div>

                                <button type="submit" id="checkout-button" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm" disabled>
                                    <i class="bi bi-check2-circle me-1"></i> Selesaikan Transaksi
                                </button>
                                <p class="text-muted text-center small mb-0 mt-2">
                                    <i class="bi bi-shield-check me-1"></i> Perhitungan & stok divalidasi aman oleh server.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="pos-mobile-bar d-xl-none" id="mobile-pos-bar">
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="bi bi-cart3 fs-3 text-success"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="mobile-cart-badge">0</span>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 0.75rem; line-height: 1;">Total Belanja</div>
                    <div class="fw-bold fs-5 text-success tabular-nums" id="mobile-total-display">Rp 0</div>
                </div>
            </div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 fw-semibold"
                data-bs-toggle="offcanvas" data-bs-target="#posCartOffcanvas" aria-controls="posCartOffcanvas">
                <span>Keranjang / Bayar</span>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
@stop

@push('js')
    <script>
        (() => {
            const config = {
                searchUrl: @json(route('pos.products')),
                initialProducts: @json($products),
                storageKey: 'pm_pos_state',
            };

            const state = { items: [], submitting: false };
            const productMap = new Map();
            let searchAbortController = null;

            const rupiah = (value) => new Intl.NumberFormat('id-ID', {
                style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
            }).format(Number(value) || 0);

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            })[char]);

            const grid = document.getElementById('product-grid');
            const emptyState = document.getElementById('product-empty');
            const cartBody = document.getElementById('cart-body');
            const cartEmpty = document.getElementById('cart-empty');
            const cartCountBadge = document.getElementById('cart-count-badge');
            const mobileCartBadge = document.getElementById('mobile-cart-badge');
            const searchInput = document.getElementById('product-search');
            const categoryFilter = document.getElementById('category-filter');
            const discountType = document.getElementById('discount-type');
            const discountValue = document.getElementById('discount-value');
            const paymentMethod = document.getElementById('payment-method');
            const paidAmount = document.getElementById('paid-amount');
            const paidWrapper = document.getElementById('paid-wrapper');
            const notesInput = document.getElementById('notes');
            const checkoutForm = document.getElementById('checkout-form');
            const checkoutButton = document.getElementById('checkout-button');
            const itemsPayload = document.getElementById('items_payload');
            const subtotalDisplay = document.getElementById('subtotal-display');
            const discountDisplay = document.getElementById('discount-display');
            const totalDisplay = document.getElementById('total-display');
            const mobileTotalDisplay = document.getElementById('mobile-total-display');
            const changeDisplay = document.getElementById('change-display');
            const shortfallAlert = document.getElementById('shortfall-alert');
            const shortfallAmount = document.getElementById('shortfall-amount');

            const registerProducts = (products) => {
                products.forEach((product) => productMap.set(product.id, product));
            };

            const renderProducts = (products) => {
                emptyState.classList.toggle('d-none', products.length > 0);

                grid.innerHTML = products.map((product) => {
                    const isPhysical = product.type === 'physical';
                    const outOfStock = isPhysical && Number(product.stock) <= 0;
                    const isLowStock = isPhysical && Number(product.stock) <= Number(product.min_stock) && Number(product.stock) > 0;

                    let stockBadge = '';
                    if (isPhysical) {
                        if (outOfStock) {
                            stockBadge = '<span class="badge badge-status-out"><i class="bi bi-x-octagon-fill"></i> Habis</span>';
                        } else if (isLowStock) {
                            stockBadge = `<span class="badge badge-status-low"><i class="bi bi-exclamation-triangle-fill"></i> Sisa ${product.stock}</span>`;
                        } else {
                            stockBadge = `<span class="badge badge-status-safe"><i class="bi bi-check-circle-fill"></i> Stok ${product.stock}</span>`;
                        }
                    } else {
                        stockBadge = `<span class="badge text-bg-info text-dark"><i class="bi bi-phone"></i> ${escapeHtml(product.type_label)}</span>`;
                    }

                    return `
                        <div class="col-6 col-sm-6 col-md-4">
                            <button type="button" class="product-card ${outOfStock ? 'disabled' : ''}"
                                data-product-id="${product.id}" ${outOfStock ? 'disabled aria-disabled="true"' : ''}
                                title="${escapeHtml(product.name)}">
                                <div>
                                    <div class="product-card-title">${escapeHtml(product.name)}</div>
                                    <div class="text-muted small mt-1"><i class="bi bi-upc me-1"></i>${escapeHtml(product.sku)}</div>
                                </div>
                                <div class="mt-2 pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                        <span class="product-card-price tabular-nums">${rupiah(product.price)}</span>
                                        ${stockBadge}
                                    </div>
                                    <div class="small text-muted mt-1 text-truncate">
                                        ${isPhysical ? `${escapeHtml(product.unit)}` : `${escapeHtml(product.provider ?? 'Digital')}`}
                                    </div>
                                </div>
                            </button>
                        </div>`;
                }).join('');
            };

            const buildDigitalFields = (item, index) => {
                if (item.type === 'pulsa') {
                    return `
                        <div class="py-1">
                            <label class="form-label small fw-semibold text-muted mb-1">
                                <i class="bi bi-telephone me-1"></i> Nomor HP Tujuan (Wajib)
                            </label>
                            <input type="tel" class="form-control form-control-sm" placeholder="Contoh: 081234567890"
                                value="${escapeHtml(item.phone_number)}" data-digital="phone" data-index="${index}" maxlength="20" inputmode="tel" required>
                        </div>`;
                }

                return `
                    <div class="py-1">
                        <label class="form-label small fw-semibold text-muted mb-1">
                            <i class="bi bi-lightning-charge me-1"></i> Data Meter / ID Pelanggan PLN
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" placeholder="ID Pelanggan"
                                    value="${escapeHtml(item.customer_id)}" data-digital="customer" data-index="${index}" maxlength="30" inputmode="numeric">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Nomor Meter"
                                    value="${escapeHtml(item.meter_number)}" data-digital="meter" data-index="${index}" maxlength="20" inputmode="numeric">
                            </div>
                        </div>
                    </div>`;
            };

            const renderCart = () => {
                const totalItemCount = state.items.reduce((sum, item) => sum + item.quantity, 0);
                cartEmpty.classList.toggle('d-none', state.items.length > 0);
                cartCountBadge.textContent = `${totalItemCount} item`;
                mobileCartBadge.textContent = `${totalItemCount}`;

                cartBody.innerHTML = state.items.map((item, index) => {
                    const lineTotal = item.price * item.quantity;

                    if (item.type === 'physical') {
                        return `
                            <tr>
                                <td>
                                    <div class="fw-semibold small text-truncate" style="max-width: 170px;" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
                                    <div class="text-muted small tabular-nums">${rupiah(item.price)} / ${escapeHtml(item.unit)}</div>
                                </td>
                                <td class="text-center">
                                    <div class="pos-qty-group">
                                        <button class="pos-qty-btn" type="button" data-dec="${index}" aria-label="Kurangi jumlah item">-</button>
                                        <input type="number" class="pos-qty-input tabular-nums" min="1" max="${item.stock}" step="1"
                                            value="${item.quantity}" data-qty="${index}" aria-label="Jumlah item">
                                        <button class="pos-qty-btn" type="button" data-inc="${index}" aria-label="Tambah jumlah item">+</button>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold tabular-nums">${rupiah(lineTotal)}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-1" data-remove="${index}" title="Hapus ${escapeHtml(item.name)}" aria-label="Hapus item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                    }

                    return `
                        <tr>
                            <td>
                                <div class="fw-semibold small text-truncate" style="max-width: 170px;">${escapeHtml(item.name)}</div>
                                <div class="text-muted small">${escapeHtml(item.type_label)} &middot; ${escapeHtml(item.provider ?? '-')}</div>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border">1 layanan</span></td>
                            <td class="text-end fw-semibold tabular-nums">${rupiah(lineTotal)}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger p-1" data-remove="${index}" title="Hapus ${escapeHtml(item.name)}" aria-label="Hapus item">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="4" class="p-2 border-bottom">${buildDigitalFields(item, index)}</td>
                        </tr>`;
                }).join('');

                renderTotals();
            };

            const currentTotals = () => {
                const subtotal = state.items.reduce((sum, item) => sum + item.price * item.quantity, 0);
                const rawValue = Number(discountValue.value) || 0;
                let discount = 0;

                if (discountType.value === 'nominal') {
                    discount = Math.min(Math.max(rawValue, 0), subtotal);
                } else if (discountType.value === 'percent') {
                    discount = Math.round(subtotal * Math.min(Math.max(rawValue, 0), 100) / 100);
                }

                const total = Math.max(0, subtotal - discount);
                const isCash = paymentMethod.value === 'cash';
                const paid = isCash ? Number(paidAmount.value) || 0 : total;
                const change = isCash ? paid - total : 0;
                const shortfall = isCash && paid < total ? total - paid : 0;

                return { subtotal, discount, total, paid, change, shortfall, isCash };
            };

            const saveState = () => {
                sessionStorage.setItem(config.storageKey, JSON.stringify({
                    items: state.items,
                    discountType: discountType.value,
                    discountValue: discountValue.value,
                    paymentMethod: paymentMethod.value,
                    paidAmount: paidAmount.value,
                    notes: notesInput.value,
                }));
            };

            const renderTotals = () => {
                const totals = currentTotals();
                subtotalDisplay.textContent = rupiah(totals.subtotal);
                discountDisplay.textContent = rupiah(totals.discount);
                totalDisplay.textContent = rupiah(totals.total);
                mobileTotalDisplay.textContent = rupiah(totals.total);

                if (totals.isCash) {
                    if (totals.shortfall > 0) {
                        shortfallAlert.classList.remove('d-none');
                        shortfallAmount.textContent = rupiah(totals.shortfall);
                        changeDisplay.textContent = '-';
                        changeDisplay.className = 'fs-5 tabular-nums text-danger';
                    } else {
                        shortfallAlert.classList.add('d-none');
                        changeDisplay.textContent = rupiah(totals.change);
                        changeDisplay.className = 'fs-5 tabular-nums text-success fw-bold';
                    }
                } else {
                    shortfallAlert.classList.add('d-none');
                    changeDisplay.textContent = '-';
                    changeDisplay.className = 'fs-5 tabular-nums text-muted';
                }

                paidWrapper.classList.toggle('d-none', !totals.isCash);

                const hasShortfall = totals.isCash && totals.paid < totals.total;
                checkoutButton.disabled = state.submitting
                    || state.items.length === 0
                    || hasShortfall;

                saveState();
            };

            const addToCart = (product) => {
                if (!product || state.submitting) return;

                if (product.type === 'physical') {
                    if (Number(product.stock) <= 0) {
                        alert(`Stok "${product.name}" habis.`);
                        return;
                    }

                    const existing = state.items.find((item) => item.type === 'physical' && item.id === product.id);

                    if (existing) {
                        if (existing.quantity + 1 > product.stock) {
                            alert(`Stok "${product.name}" hanya tersisa ${product.stock} ${product.unit}.`);
                            return;
                        }
                        existing.quantity += 1;
                    } else {
                        state.items.push({ ...product, quantity: 1 });
                    }
                } else {
                    if (state.items.some((item) => item.id === product.id)) {
                        alert(`Layanan "${product.name}" sudah ada di keranjang.`);
                        return;
                    }

                    state.items.push({
                        ...product,
                        quantity: 1,
                        phone_number: '',
                        customer_id: '',
                        meter_number: '',
                    });
                }

                renderCart();
            };

            const validateCart = () => {
                for (const item of state.items) {
                    if (item.type === 'pulsa' && !String(item.phone_number ?? '').trim()) {
                        alert(`Nomor HP tujuan untuk ${item.name} wajib diisi.`);
                        return false;
                    }

                    if (item.type === 'token' && !String(item.customer_id ?? '').trim() && !String(item.meter_number ?? '').trim()) {
                        alert(`ID pelanggan atau nomor meter untuk ${item.name} wajib diisi.`);
                        return false;
                    }
                }

                const totals = currentTotals();

                if (totals.total < 0) {
                    alert('Total transaksi tidak boleh negatif.');
                    return false;
                }

                if (totals.isCash && totals.paid < totals.total) {
                    alert(`Uang tunai yang diterima kurang ${rupiah(totals.shortfall)} dari total tagihan.`);
                    return false;
                }

                return true;
            };

            checkoutForm.addEventListener('submit', (event) => {
                event.preventDefault();

                if (state.submitting) return;

                if (state.items.length === 0) {
                    alert('Keranjang masih kosong.');
                    return;
                }

                if (!validateCart()) return;

                itemsPayload.value = JSON.stringify(state.items.map((item) => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    phone_number: item.phone_number || null,
                    customer_id: item.customer_id || null,
                    meter_number: item.meter_number || null,
                })));

                state.submitting = true;
                checkoutButton.disabled = true;
                checkoutButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan transaksi...';
                saveState();

                checkoutForm.submit();
            });

            const updateQuantity = (index, quantity) => {
                const item = state.items[index];
                if (!item) return;

                const maximum = item.type === 'physical' ? Math.max(1, item.stock) : 1;
                item.quantity = Math.min(Math.max(1, Math.round(Number(quantity)) || 1), maximum);
                renderCart();
            };

            cartBody.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove]');
                if (removeButton) {
                    const idx = Number(removeButton.dataset.remove);
                    const item = state.items[idx];
                    if (item && confirm(`Hapus "${item.name}" dari keranjang?`)) {
                        state.items.splice(idx, 1);
                        renderCart();
                    }
                    return;
                }

                const increaseButton = event.target.closest('[data-inc]');
                if (increaseButton) {
                    const index = Number(increaseButton.dataset.inc);
                    updateQuantity(index, (state.items[index]?.quantity ?? 1) + 1);
                    return;
                }

                const decreaseButton = event.target.closest('[data-dec]');
                if (decreaseButton) {
                    const index = Number(decreaseButton.dataset.dec);
                    updateQuantity(index, (state.items[index]?.quantity ?? 1) - 1);
                }
            });

            cartBody.addEventListener('change', (event) => {
                const input = event.target.closest('[data-qty]');
                if (input) {
                    updateQuantity(Number(input.dataset.qty), Number(input.value));
                }
            });

            cartBody.addEventListener('input', (event) => {
                const input = event.target.closest('[data-digital]');
                if (!input) return;

                const index = Number(input.dataset.index);
                const fieldMap = { phone: 'phone_number', customer: 'customer_id', meter: 'meter_number' };
                const field = fieldMap[input.dataset.digital];

                if (state.items[index] && field) {
                    state.items[index][field] = input.value;
                    saveState();
                }
            });

            discountType.addEventListener('change', () => {
                discountValue.disabled = discountType.value === '';
                discountValue.max = discountType.value === 'percent' ? '100' : '';

                if (discountType.value === '') {
                    discountValue.value = '0';
                }

                renderTotals();
            });

            discountValue.addEventListener('input', renderTotals);
            paymentMethod.addEventListener('change', () => {
                const isCash = paymentMethod.value === 'cash';
                if (!isCash) {
                    paidAmount.value = String(currentTotals().total);
                }
                renderTotals();
            });
            paidAmount.addEventListener('input', renderTotals);
            notesInput.addEventListener('input', saveState);
            window.addEventListener('pagehide', saveState);

            document.querySelectorAll('[data-cash]').forEach((button) => {
                button.addEventListener('click', () => {
                    const totals = currentTotals();
                    paidAmount.value = button.dataset.cash === 'exact'
                        ? String(totals.total)
                        : button.dataset.cash;
                    renderTotals();
                });
            });

            document.getElementById('clear-cart').addEventListener('click', () => {
                if (state.items.length === 0 || !confirm('Kosongkan seluruh keranjang belanja?')) {
                    return;
                }

                state.items = [];
                discountType.value = '';
                discountValue.value = '0';
                discountValue.disabled = true;
                paidAmount.value = '0';
                notesInput.value = '';
                renderCart();
            });

            grid.addEventListener('click', (event) => {
                const button = event.target.closest('[data-product-id]');
                if (!button || button.disabled) return;
                addToCart(productMap.get(Number(button.dataset.productId)));
            });

            let searchTimer = null;

            const runSearch = async (addIfSingle = false) => {
                if (searchAbortController) {
                    searchAbortController.abort();
                }
                searchAbortController = new AbortController();

                const params = new URLSearchParams();
                const term = searchInput.value.trim();

                if (term !== '') params.set('q', term);
                if (categoryFilter.value !== '') params.set('category_id', categoryFilter.value);

                emptyState.classList.add('d-none');
                grid.innerHTML = '<div class="col-12 text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2 text-success"></span>Memuat daftar barang...</div>';

                try {
                    const response = await fetch(`${config.searchUrl}?${params.toString()}`, {
                        headers: { Accept: 'application/json' },
                        signal: searchAbortController.signal,
                    });

                    if (!response.ok) throw new Error('Gagal memuat barang.');

                    const payload = await response.json();
                    const products = Array.isArray(payload.data) ? payload.data : [];
                    registerProducts(products);
                    renderProducts(products);

                    if (addIfSingle && products.length === 1) {
                        addToCart(products[0]);
                        searchInput.value = '';
                    }
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    grid.innerHTML = '';
                    emptyState.classList.remove('d-none');
                    emptyState.innerHTML = '<div class="card-body"><i class="bi bi-exclamation-triangle text-danger display-6 mb-2"></i><p class="text-danger mb-0">Gagal mencari barang. Periksa koneksi atau coba lagi.</p></div>';
                }
            };

            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => runSearch(false), 300);
            });

            searchInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(searchTimer);
                    runSearch(true);
                }
            });

            categoryFilter.addEventListener('change', () => runSearch(false));

            window.addEventListener('keydown', (event) => {
                const isFormInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);

                if (event.key === 'F2') {
                    event.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                } else if (event.key === 'F4' && !isFormInput) {
                    event.preventDefault();
                    document.getElementById('clear-cart')?.click();
                } else if (event.key === 'F8') {
                    event.preventDefault();
                    const offcanvasEl = document.getElementById('posCartOffcanvas');
                    if (window.innerWidth < 1200 && offcanvasEl && window.bootstrap?.Offcanvas) {
                        const offcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                        offcanvas.show();
                    }
                    if (paymentMethod.value === 'cash') {
                        paidAmount.focus();
                        paidAmount.select();
                    } else {
                        checkoutButton.focus();
                    }
                }
            });

            const restoreState = () => {
                const raw = sessionStorage.getItem(config.storageKey);
                if (!raw) return;

                try {
                    const saved = JSON.parse(raw);

                    if (Array.isArray(saved.items)) {
                        state.items = saved.items
                            .filter((item) => item && Number.isFinite(Number(item.id)) && Number.isFinite(Number(item.price)))
                            .map((item) => ({
                                ...item,
                                quantity: item.type === 'physical' ? Math.max(1, Number(item.quantity) || 1) : 1,
                                phone_number: item.phone_number ?? '',
                                customer_id: item.customer_id ?? '',
                                meter_number: item.meter_number ?? '',
                            }));
                    }

                    if (typeof saved.discountType === 'string') discountType.value = saved.discountType;
                    if (saved.discountValue !== undefined) discountValue.value = saved.discountValue;
                    if (typeof saved.paymentMethod === 'string') paymentMethod.value = saved.paymentMethod;
                    if (saved.paidAmount !== undefined) paidAmount.value = saved.paidAmount;
                    if (typeof saved.notes === 'string') notesInput.value = saved.notes;
                } catch (error) {
                    sessionStorage.removeItem(config.storageKey);
                }
            };

            registerProducts(config.initialProducts);
            renderProducts(config.initialProducts);
            restoreState();
            discountValue.disabled = discountType.value === '';
            discountValue.max = discountType.value === 'percent' ? '100' : '';
            renderCart();
            searchInput.focus();
        })();
    </script>
@endpush
